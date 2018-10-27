<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsFeedback\Base\FeedbackDevTranslateForm;
use Iliich246\YicmsFeedback\Base\FeedbackException;
use Iliich246\YicmsFeedback\Base\FeedbackStages;
use Iliich246\YicmsFeedback\Base\FeedbackStagesDevTranslateForm;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\Base\Feedback;

/**
 * Class DeveloperController
 *
 * Controller for developer section in feedback module
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DeveloperController extends Controller
{
    /** @inheritdoc */
    public $layout = '@yicms-common/Views/layouts/developer';
    /** @inheritdoc */
    public $defaultAction = 'list';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            'root' => [
//                'class' => DevFilter::className(),
//                'except' => ['login-as-root'],
//            ],
        ];
    }

    /**
     * Returns list of oll feedback
     * @return string
     */
    public function actionList()
    {
        $feedback = Feedback::find()->orderBy([
            'feedback_order' => SORT_ASC
        ])->all();

        return $this->render('/developer/list', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Creates new feedback
     * @return string|\yii\web\Response
     * @throws FeedbackException
     */
    public function actionCreateFeedback()
    {
        $feedback = new Feedback();
        $feedback->scenario = Feedback::SCENARIO_CREATE;

        if ($feedback->load(Yii::$app->request->post()) && $feedback->validate()) {

            if ($feedback->create()) {
                return $this->redirect(Url::toRoute(['update-feedback', 'id' => $feedback->id]));
            } else {
                //TODO: add bootbox error
            }
        }

        return $this->render('/developer/create-update-feedback', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Updates feedback
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionUpdateFeedback($id)
    {
        $feedback = Feedback::getInstance($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedback->scenario = Feedback::SCENARIO_UPDATE;

        if ($feedback->load(Yii::$app->request->post()) && $feedback->validate()) {

            if ($feedback->save()) {
                $success = true;
            } else {
                $success = false;
            }

            return $this->render('/developer/create-update-feedback', [
                'feedback' => $feedback,
                'success'  => $success
            ]);
        }

        return $this->render('/developer/create-update-feedback', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Displays page for work with admin translations of feedback
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionFeedbackTranslates($id)
    {
        $feedback = Feedback::getInstance($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $languages = Language::getInstance()->usedLanguages();

        $translateModels = [];

        foreach($languages as $key => $language) {
            $essenceTranslate = new FeedbackDevTranslateForm();
            $essenceTranslate->setLanguage($language);
            $essenceTranslate->setFeedback($feedback);
            $essenceTranslate->loadFromDb();

            $translateModels[$key] = $essenceTranslate;
        }

        if (Model::loadMultiple($translateModels, Yii::$app->request->post()) &&
            Model::validateMultiple($translateModels)) {

            /** @var FeedbackDevTranslateForm $translateModel */
            foreach($translateModels as $key=>$translateModel) {
                $translateModel->save();
            }

            return $this->render('/developer/feedback-translates', [
                'feedback'        => $feedback,
                'translateModels' => $translateModels,
                'success'         => true,
            ]);
        }

        return $this->render('/developer/feedback-translates', [
            'feedback'        => $feedback,
            'translateModels' => $translateModels,
        ]);
    }

    /**
     * Action for delete feedback
     * @param $id
     * @param bool $deletePass
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws FeedbackException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteFeedback($id, $deletePass = false)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        $feedback = Feedback::getInstance($id);

        if (!$feedback)
            throw new NotFoundHttpException('Wrong feedback id');

        if ($feedback->isConstraints())
            if (!Yii::$app->security->validatePassword($deletePass, CommonHashForm::DEV_HASH))
                throw new FeedbackException('Wrong dev password');

        if ($feedback->delete())
            return $this->redirect(Url::toRoute(['list']));

        throw new FeedbackException('Delete error');
    }

    /**
     * Action for up feedback order
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionFeedbackUpOrder($id)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        $feedback = Feedback::getInstance($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedback->configToChangeOfOrder();
        $feedback->upOrder();

        $feedback = Feedback::find()->orderBy([
            'feedback_order' => SORT_ASC
        ])->all();

        return $this->render('/pjax/update-feedback-list-container', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Action for down feedback order
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionFeedbackDownOrder($id)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        $feedback = Feedback::getInstance($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedback->configToChangeOfOrder();
        $feedback->downOrder();

        $feedback = Feedback::find()->orderBy([
            'feedback_order' => SORT_ASC
        ])->all();

        return $this->render('/pjax/update-feedback-list-container', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Returns list of oll feedback stages for concrete feedback
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionStagesList($id)
    {
        $feedback = Feedback::getInstance($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedbackStages = FeedbackStages::find()->orderBy([
            'feedback_id' => $feedback->id,
            'stage_order' => SORT_ASC
        ])->all();

        return $this->render('/developer/stages-list', [
            'feedback'       => $feedback,
            'feedbackStages' => $feedbackStages,
        ]);
    }

    /**
     * Creates new feedback stage
     * @param $id
     * @return string|\yii\web\Response
     * @throws FeedbackException
     * @throws NotFoundHttpException
     */
    public function actionCreateStage($id)
    {
        $feedback = Feedback::getInstance($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedbackStage = new FeedbackStages();
        $feedbackStage->setFeedback($feedback);
        $feedbackStage->scenario = FeedbackStages::SCENARIO_CREATE;

        if ($feedbackStage->load(Yii::$app->request->post()) && $feedbackStage->validate()) {

            if ($feedbackStage->create()) {
                return $this->redirect(Url::toRoute(['update-stage', 'id' => $feedback->id]));
            } else {
                //TODO: add bootbox error
            }
        }

        return $this->render('/developer/create-update-stage', [
            'feedbackStage' => $feedbackStage,
        ]);
    }

    /**
     * Update feedback stage
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpdateStage($id)
    {
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        $feedbackStage->scenario = FeedbackStages::SCENARIO_UPDATE;

        if ($feedbackStage->load(Yii::$app->request->post()) && $feedbackStage->validate()) {

            if ($feedbackStage->save()) {
                $success = true;
            } else {
                $success = false;
            }

            return $this->render('/developer/create-update-stage', [
                'feedbackStage' => $feedbackStage,
                'success'  => $success
            ]);
        }

        return $this->render('/developer/create-update-stage', [
            'feedbackStage' => $feedbackStage,
        ]);
    }

    /**
     * Displays page for work with admin translations of feedback states
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionStageTranslates($id)
    {
        /** @var FeedbackStages $feedbackStage */
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        $languages = Language::getInstance()->usedLanguages();

        $translateModels = [];

        foreach($languages as $key => $language) {
            $essenceTranslate = new FeedbackStagesDevTranslateForm();
            $essenceTranslate->setLanguage($language);
            $essenceTranslate->setFeedbackStage($feedbackStage);
            $essenceTranslate->loadFromDb();

            $translateModels[$key] = $essenceTranslate;
        }

        if (Model::loadMultiple($translateModels, Yii::$app->request->post()) &&
            Model::validateMultiple($translateModels)) {

            /** @var FeedbackDevTranslateForm $translateModel */
            foreach($translateModels as $key=>$translateModel) {
                $translateModel->save();
            }

            return $this->render('/developer/feedback-stages-translates', [
                'feedbackStage'   => $feedbackStage,
                'translateModels' => $translateModels,
                'success'         => true,
            ]);
        }

        return $this->render('/developer/feedback-stages-translates', [
            'feedbackStage'   => $feedbackStage,
            'translateModels' => $translateModels,
        ]);
    }

    public function actionDeleteStage($id)
    {

    }


    public function actionStageUpOrder($id)
    {

    }

    public function actionStageDownOrder($id)
    {

    }
}
