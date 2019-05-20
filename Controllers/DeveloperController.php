<?php

namespace Iliich246\YicmsFeedback\Controllers;

use Iliich246\YicmsFeedback\Base\FeedbackConfigDb;
use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Base\DevFilter;
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
use Iliich246\YicmsFeedback\Base\FeedbackException;
use Iliich246\YicmsFeedback\Base\FeedbackDevTranslateForm;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFields\DevInputFieldsGroup;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;
use Iliich246\YicmsFeedback\InputFiles\InputFilesBlock;
use Iliich246\YicmsFeedback\InputFiles\DevInputFilesGroup;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;
use Iliich246\YicmsFeedback\InputImages\InputImagesBlock;
use Iliich246\YicmsFeedback\InputImages\DevInputImagesGroup;
use Iliich246\YicmsFeedback\InputImages\InputImagesDevModalWidget;
use Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate;
use Iliich246\YicmsFeedback\InputConditions\DevInputConditionsGroup;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

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
            'dev' => [
                'class' => DevFilter::class,
                'redirect' => function() {
                    return $this->redirect(Url::home());
                }
            ],
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
        $feedback = Feedback::getInstanceById($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        $feedback->annotate();

        $feedback->scenario = Feedback::SCENARIO_UPDATE;

        if ($feedback->loadDev(Yii::$app->request->post()) && $feedback->validateDev()) {

            if ($feedback->save(false)) {
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
        $feedback = Feedback::getInstanceById($id);

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

        $feedback = Feedback::getInstanceById($id);

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

        $feedback = Feedback::getInstanceById($id);

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

        $feedback = Feedback::getInstanceById($id);

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
     * Renders feedback stage templates page
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function actionFeedbackPageTemplates($id)
    {
        $feedback = Feedback::getInstanceById($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        //initialize fields group
        $devFieldGroup = new DevFieldsGroup();
        $devFieldGroup->setFieldTemplateReference($feedback->getFieldTemplateReference());
        $devFieldGroup->initialize(Yii::$app->request->post('_fieldTemplateId'));

        //try to load validate and save field via pjax
        if ($devFieldGroup->load(Yii::$app->request->post()) && $devFieldGroup->validate()) {

            if (!$devFieldGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return FieldsDevModalWidget::widget([
                'devFieldGroup' => $devFieldGroup,
                'dataSaved'     => true,
            ]);
        }

        $devFilesGroup = new DevFilesGroup();
        $devFilesGroup->setFilesTemplateReference($feedback->getFileTemplateReference());
        $devFilesGroup->initialize(Yii::$app->request->post('_fileTemplateId'));

        //try to load validate and save field via pjax
        if ($devFilesGroup->load(Yii::$app->request->post()) && $devFilesGroup->validate()) {

            if (!$devFilesGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return FilesDevModalWidget::widget([
                'devFilesGroup' => $devFilesGroup,
                'dataSaved'     => true,
            ]);
        }

        $devImagesGroup = new DevImagesGroup();
        $devImagesGroup->setImagesTemplateReference($feedback->getImageTemplateReference());
        $devImagesGroup->initialize(Yii::$app->request->post('_imageTemplateId'));

        //try to load validate and save image block via pjax
        if ($devImagesGroup->load(Yii::$app->request->post()) && $devImagesGroup->validate()) {

            if (!$devImagesGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return ImagesDevModalWidget::widget([
                'devImagesGroup' => $devImagesGroup,
                'dataSaved'      => true,
            ]);
        }

        $devConditionsGroup = new DevConditionsGroup();
        $devConditionsGroup->setConditionsTemplateReference($feedback->getConditionTemplateReference());
        $devConditionsGroup->initialize(Yii::$app->request->post('_conditionTemplateId'));

        //try to load validate and save image block via pjax
        if ($devConditionsGroup->load(Yii::$app->request->post()) && $devConditionsGroup->validate()) {

            if (!$devConditionsGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return ConditionsDevModalWidget::widget([
                'devConditionsGroup' => $devConditionsGroup,
                'dataSaved'          => true,
            ]);
        }

        $fieldTemplatesTranslatable = FieldTemplate::getListQuery($feedback->getFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_TRANSLATABLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $fieldTemplatesSingle = FieldTemplate::getListQuery($feedback->getFieldTemplateReference())
            ->andWhere(['language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE])
            ->orderBy([FieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $filesBlocks = FilesBlock::getListQuery($feedback->getFileTemplateReference())
            ->orderBy([FilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $imagesBlocks = ImagesBlock::getListQuery($feedback->getImageTemplateReference())
            ->orderBy([ImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $conditionTemplates = ConditionTemplate::getListQuery($feedback->getConditionTemplateReference())
            ->orderBy([ConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $feedback->annotate();

        return $this->render('/developer/feedback_page_templates', [
            'feedback'                   => $feedback,
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
    public function actionFeedbackInputTemplates($id)
    {
        $feedback = Feedback::getInstanceById($id);

        if (!$feedback) throw new NotFoundHttpException('Wrong id of feedback = ' . $id);

        //initialize fields group
        $devInputFieldGroup = new DevInputFieldsGroup();
        $devInputFieldGroup->setInputFieldTemplateReference($feedback->getInputFieldTemplateReference());
        $devInputFieldGroup->initialize(Yii::$app->request->post('_inputFieldTemplateId'));

        //try to load validate and save field via pjax
        if ($devInputFieldGroup->load(Yii::$app->request->post()) && $devInputFieldGroup->validate()) {

            if (!$devInputFieldGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return InputFieldsDevModalWidget::widget([
                'devInputFieldGroup' => $devInputFieldGroup,
                'dataSaved'          => true,
            ]);
        }

        $devInputFilesGroup = new DevInputFilesGroup();
        $devInputFilesGroup->setInputFilesTemplateReference($feedback->getInputFileTemplateReference());
        $devInputFilesGroup->initialize(Yii::$app->request->post('_inputFilesBlockId'));

        //try to load validate and save field via pjax
        if ($devInputFilesGroup->load(Yii::$app->request->post()) && $devInputFilesGroup->validate()) {
            if (!$devInputFilesGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return InputFilesDevModalWidget::widget([
                'devInputFilesGroup' => $devInputFilesGroup,
                'dataSaved'          => true,
            ]);
        }

        $devInputImagesGroup = new DevInputImagesGroup();
        $devInputImagesGroup->setInputImagesTemplateReference($feedback->getInputImageTemplateReference());
        $devInputImagesGroup->initialize(Yii::$app->request->post('_inputImageBlockId'));

        //try to load validate and save image block via pjax
        if ($devInputImagesGroup->load(Yii::$app->request->post()) && $devInputImagesGroup->validate()) {
            if (!$devInputImagesGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return InputImagesDevModalWidget::widget([
                'devInputImagesGroup' => $devInputImagesGroup,
                'dataSaved'           => true,
            ]);
        }

        $devInputConditionsGroup = new DevInputConditionsGroup();
        $devInputConditionsGroup->setInputConditionsTemplateReference($feedback->getInputConditionTemplateReference());
        $devInputConditionsGroup->initialize(Yii::$app->request->post('_inputConditionTemplateId'));

        //try to load validate and save image block via pjax
        if ($devInputConditionsGroup->load(Yii::$app->request->post()) && $devInputConditionsGroup->validate()) {

            if (!$devInputConditionsGroup->save()) {
                //TODO: bootbox error
            }

            $feedback->annotate();

            return InputConditionsDevModalWidget::widget([
                'devInputConditionsGroup' => $devInputConditionsGroup,
                'dataSaved'               => true,
            ]);
        }

        $inputFieldTemplates = InputFieldTemplate::getListQuery($feedback->getInputFieldTemplateReference())
            ->orderBy([InputFieldTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $inputFilesBlocks = InputFilesBlock::getListQuery($feedback->getInputFileTemplateReference())
            ->orderBy([InputFilesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $inputImagesBlocks = InputImagesBlock::getListQuery($feedback->getInputImageTemplateReference())
            ->orderBy([InputImagesBlock::getOrderFieldName() => SORT_ASC])
            ->all();

        $inputConditionTemplates = InputConditionTemplate::getListQuery($feedback->getInputConditionTemplateReference())
            ->orderBy([InputConditionTemplate::getOrderFieldName() => SORT_ASC])
            ->all();

        $feedback->annotate();

        return $this->render('/developer/feedback_input_templates', [
            'feedback'                => $feedback,
            'devInputFieldGroup'      => $devInputFieldGroup,
            'inputFieldTemplates'     => $inputFieldTemplates,
            'devInputFilesGroup'      => $devInputFilesGroup,
            'inputFilesBlocks'        => $inputFilesBlocks,
            'devInputImagesGroup'     => $devInputImagesGroup,
            'inputImagesBlocks'       => $inputImagesBlocks,
            'devInputConditionsGroup' => $devInputConditionsGroup,
            'inputConditionTemplates' => $inputConditionTemplates
        ]);
    }

    /**
     * Maintenance action for feedback module
     * @return string
     * @throws FeedbackException
     */
    public function actionMaintenance()
    {
        $config = FeedbackConfigDb::getInstance();

        if ($config->load(Yii::$app->request->post()) && $config->validate()) {
            if ($config->save()) {
                return $this->render('/developer/maintenance', [
                    'config'  => $config,
                    'success' => true,
                ]);
            }

            throw new FeedbackException('Can`t save data in database');
        }

        return $this->render('/developer/maintenance', [
            'config' => $config
        ]);
    }
}
