<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Base\CommonHashForm;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\DevFieldsGroup;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Files\DevFilesGroup;
use Iliich246\YicmsCommon\Files\FilesDevModalWidget;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Images\DevImagesGroup;
use Iliich246\YicmsCommon\Images\ImagesDevModalWidget;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsCommon\Conditions\DevConditionsGroup;
use Iliich246\YicmsCommon\Conditions\ConditionsDevModalWidget;
use Iliich246\YicmsFeedback\Base\Feedback;
use Iliich246\YicmsFeedback\Base\FeedbackStages;
use Iliich246\YicmsFeedback\Base\FeedbackException;
use Iliich246\YicmsFeedback\Base\FeedbackDevTranslateForm;
use Iliich246\YicmsFeedback\Base\FeedbackStagesDevTranslateForm;

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

        if ($feedback->loadDev(Yii::$app->request->post()) && $feedback->validateDev()) {

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

        if ($feedback->loadDev(Yii::$app->request->post()) && $feedback->validateDev()) {

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

        $feedbackStages = FeedbackStages::find()
            ->where([
                'feedback_id' => $feedback->id,
            ])
            ->orderBy([
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

        if ($feedbackStage->loadDev(Yii::$app->request->post()) && $feedbackStage->validateDev()) {

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
        /** @var FeedbackStages $feedbackStage */
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        $feedbackStage->scenario = FeedbackStages::SCENARIO_UPDATE;

        if ($feedbackStage->loadDev(Yii::$app->request->post()) && $feedbackStage->validateDev()) {

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

    /**
     * Action for up feedback stage order
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionStageUpOrder($id)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var FeedbackStages $feedbackStage */
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        $feedbackStage->configToChangeOfOrder();
        $feedbackStage->upOrder();

        $feedback = Feedback::getInstance($feedbackStage->feedback_id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedbackStages = FeedbackStages::find()
            ->where([
                'feedback_id' => $feedback->id,
            ])
            ->orderBy([
                'stage_order' => SORT_ASC
            ])->all();

        return $this->render('/pjax/update-feedback-stages-list-container', [
            'feedbackStages' => $feedbackStages,
        ]);
    }

    /**
     * Action for down feedback stage order
     * @param $id
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionStageDownOrder($id)
    {
        if (!Yii::$app->request->isPjax) throw new BadRequestHttpException();

        /** @var FeedbackStages $feedbackStage */
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        $feedbackStage->configToChangeOfOrder();
        $feedbackStage->downOrder();

        $feedback = Feedback::getInstance($feedbackStage->feedback_id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedbackStages = FeedbackStages::find()
            ->where([
                'feedback_id' => $feedback->id,
            ])
            ->orderBy([
                'stage_order' => SORT_ASC
            ])->all();

        return $this->render('/pjax/update-feedback-stages-list-container', [
            'feedbackStages' => $feedbackStages,
        ]);
    }

    /**
     * Renders feedback stage templates page
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionStagePageTemplates($id)
    {
        /** @var FeedbackStages $feedbackStage */
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        //initialize fields group
        $devFieldGroup = new DevFieldsGroup();
        $devFieldGroup->setFieldTemplateReference($feedbackStage->getFieldTemplateReference());
        $devFieldGroup->initialize(Yii::$app->request->post('_fieldTemplateId'));

        //try to load validate and save field via pjax
        if ($devFieldGroup->load(Yii::$app->request->post()) && $devFieldGroup->validate()) {

            if (!$devFieldGroup->save()) {
                //TODO: bootbox error
            }

            return FieldsDevModalWidget::widget([
                'devFieldGroup' => $devFieldGroup,
                'dataSaved'     => true,
            ]);
        }

        $devFilesGroup = new DevFilesGroup();
        $devFilesGroup->setFilesTemplateReference($feedbackStage->getFileTemplateReference());
        $devFilesGroup->initialize(Yii::$app->request->post('_fileTemplateId'));

        //try to load validate and save field via pjax
        if ($devFilesGroup->load(Yii::$app->request->post()) && $devFilesGroup->validate()) {

            if (!$devFilesGroup->save()) {
                //TODO: bootbox error
            }

            return FilesDevModalWidget::widget([
                'devFilesGroup' => $devFilesGroup,
                'dataSaved'     => true,
            ]);
        }

        $devImagesGroup = new DevImagesGroup();
        $devImagesGroup->setImagesTemplateReference($feedbackStage->getImageTemplateReference());
        $devImagesGroup->initialize(Yii::$app->request->post('_imageTemplateId'));

        //try to load validate and save image block via pjax
        if ($devImagesGroup->load(Yii::$app->request->post()) && $devImagesGroup->validate()) {

            if (!$devImagesGroup->save()) {
                //TODO: bootbox error
            }

            return ImagesDevModalWidget::widget([
                'devImagesGroup' => $devImagesGroup,
                'dataSaved'      => true,
            ]);
        }

        $devConditionsGroup = new DevConditionsGroup();
        $devConditionsGroup->setConditionsTemplateReference($feedbackStage->getConditionTemplateReference());
        $devConditionsGroup->initialize(Yii::$app->request->post('_conditionTemplateId'));

        //try to load validate and save image block via pjax
        if ($devConditionsGroup->load(Yii::$app->request->post()) && $devConditionsGroup->validate()) {

            if (!$devConditionsGroup->save()) {
                //TODO: bootbox error
            }

            return ConditionsDevModalWidget::widget([
                'devConditionsGroup' => $devConditionsGroup,
                'dataSaved'          => true,
            ]);
        }

        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($feedbackStage->getFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $fieldTemplatesSingle = FieldTemplate::getListQuery($feedbackStage->getFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $filesBlocks = FilesBlock::getListQuery($feedbackStage->getFileTemplateReference())
            ->orderBy([FilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $imagesBlocks = ImagesBlock::getListQuery($feedbackStage->getImageTemplateReference())
            ->orderBy([ImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $conditionTemplates = ConditionTemplate::getListQuery($feedbackStage->getConditionTemplateReference())
            ->orderBy([ConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/developer/stage_page_templates', [
            'feedbackStage'             => $feedbackStage,
            'devFieldGroup'              => $devFieldGroup,
            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
            'devFilesGroup'              => $devFilesGroup,
            'filesBlocks'                => $filesBlocks,
            'devImagesGroup'             => $devImagesGroup,
            'imagesBlocks'               => $imagesBlocks,
            'devConditionsGroup'         => $devConditionsGroup,
            'conditionTemplates'         => $conditionTemplates
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionStageInputTemplates($id)
    {
        /** @var FeedbackStages $feedbackStage */
        $feedbackStage = FeedbackStages::findOne($id);

        if (!$feedbackStage) throw new NotFoundHttpException('Wrong id of feedback stage = ' . $id);

        //initialize fields group
        $devFieldGroup = new DevFieldsGroup();
        $devFieldGroup->setFieldTemplateReference($feedbackStage->getInputFieldTemplateReference());
        $devFieldGroup->initialize(Yii::$app->request->post('_fieldTemplateId'));

        //try to load validate and save field via pjax
        if ($devFieldGroup->load(Yii::$app->request->post()) && $devFieldGroup->validate()) {

            if (!$devFieldGroup->save()) {
                //TODO: bootbox error
            }

            return FieldsDevModalWidget::widget([
                'devFieldGroup' => $devFieldGroup,
                'dataSaved'     => true,
            ]);
        }

        $devFilesGroup = new DevFilesGroup();
        $devFilesGroup->setFilesTemplateReference($feedbackStage->getInputFieldTemplateReference());
        $devFilesGroup->initialize(Yii::$app->request->post('_fileTemplateId'));

        //try to load validate and save field via pjax
        if ($devFilesGroup->load(Yii::$app->request->post()) && $devFilesGroup->validate()) {

            if (!$devFilesGroup->save()) {
                //TODO: bootbox error
            }

            return FilesDevModalWidget::widget([
                'devFilesGroup' => $devFilesGroup,
                'dataSaved'     => true,
            ]);
        }

        $devImagesGroup = new DevImagesGroup();
        $devImagesGroup->setImagesTemplateReference($feedbackStage->getInputImageTemplateReference());
        $devImagesGroup->initialize(Yii::$app->request->post('_imageTemplateId'));

        //try to load validate and save image block via pjax
        if ($devImagesGroup->load(Yii::$app->request->post()) && $devImagesGroup->validate()) {

            if (!$devImagesGroup->save()) {
                //TODO: bootbox error
            }

            return ImagesDevModalWidget::widget([
                'devImagesGroup' => $devImagesGroup,
                'dataSaved'      => true,
            ]);
        }

        $devConditionsGroup = new DevConditionsGroup();
        $devConditionsGroup->setConditionsTemplateReference($feedbackStage->getInputConditionTemplateReference());
        $devConditionsGroup->initialize(Yii::$app->request->post('_conditionTemplateId'));

        //try to load validate and save image block via pjax
        if ($devConditionsGroup->load(Yii::$app->request->post()) && $devConditionsGroup->validate()) {

            if (!$devConditionsGroup->save()) {
                //TODO: bootbox error
            }

            return ConditionsDevModalWidget::widget([
                'devConditionsGroup' => $devConditionsGroup,
                'dataSaved'          => true,
            ]);
        }

        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($feedbackStage->getInputFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $fieldTemplatesSingle = FieldTemplate::getListQuery($feedbackStage->getInputFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $filesBlocks = FilesBlock::getListQuery($feedbackStage->getInputFileTemplateReference())
            ->orderBy([FilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $imagesBlocks = ImagesBlock::getListQuery($feedbackStage->getInputImageTemplateReference())
            ->orderBy([ImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $conditionTemplates = ConditionTemplate::getListQuery($feedbackStage->getInputConditionTemplateReference())
            ->orderBy([ConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        return $this->render('/developer/stage_input_templates', [
            'feedbackStage'             => $feedbackStage,
            'devFieldGroup'              => $devFieldGroup,
            'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
            'fieldTemplatesSingle'       => $fieldTemplatesSingle,
            'devFilesGroup'              => $devFilesGroup,
            'filesBlocks'                => $filesBlocks,
            'devImagesGroup'             => $devImagesGroup,
            'imagesBlocks'               => $imagesBlocks,
            'devConditionsGroup'         => $devConditionsGroup,
            'conditionTemplates'         => $conditionTemplates
        ]);
    }
}
