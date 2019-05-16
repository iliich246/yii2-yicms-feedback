<?php

namespace app\yicms\Feedback\Controllers;

use app\yicms\Feedback\Models\Debug;
use Iliich246\YicmsFeedback\Base\FeedbackException;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Fields\FieldsGroup;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Conditions\ConditionsGroup;
use Iliich246\YicmsFeedback\Base\Feedback;
use Iliich246\YicmsFeedback\Base\FeedbackState;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFiles\InputFile;
use Iliich246\YicmsFeedback\InputFiles\InputFilesBlock;
use Iliich246\YicmsFeedback\InputImages\InputImagesBlock;
use Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate;

/**
 * Class AdminController
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class AdminController extends Controller
{
    /** @inheritdoc */
    public $defaultAction = 'view-feedback';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->layout = CommonModule::getInstance()->yicmsLocation . '/Common/Views/layouts/admin';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            'root' => [
//                'class' => AdminFilter::className(),
//                'except' => ['login-as-root'],
//            ],
        ];
    }

    /**
     * @param $feedbackId
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionElementsList($feedbackId)
    {
        /** @var Feedback $feedback */
        $feedback = Feedback::getInstanceById($feedbackId);

        if (!$feedback) throw new NotFoundHttpException('Wrong feedback ID');

        /** @var Feedback $className */
        //$className = $feedback->getAnnotationFileNamespace() . '\\' .$feedback->getAnnotationFileName();

        //$ss = 111;
        //if (class_exists($className))
//            $ss = Debug::getInstance();

        //$feedback->getByName($feedback->program_name);

        if ($feedback->active == false && !CommonModule::isUnderDev())
            throw new NotFoundHttpException('Wrong feedback ID');

        $feedbackStatesQuery = $feedback->statesQuery();

        $feedbackStatesQuery->orderBy([
            'created_at' => SORT_ASC
        ]);

        $pagination = new Pagination([
            'totalCount'      => $feedbackStatesQuery->count(),
            'defaultPageSize' => $feedback->count_states_on_page,
        ]);

        $feedbackStates = $feedbackStatesQuery->offset($pagination->offset)
                                              ->limit($pagination->limit)
                                              ->all();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/admin/elements-list',[
            'feedback'       => $feedback,
            'feedbackStates' => $feedbackStates,
            'pagination'     => $pagination,
        ]);
    }

    /**
     * Action for view state
     * @param $feedbackId
     * @param $stateId
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionViewState($feedbackId, $stateId)
    {
        $feedback = Feedback::getInstanceById($feedbackId);

        if (!$feedback) throw new NotFoundHttpException('Wrong feedback ID');

        if ($feedback->active == false && !CommonModule::isUnderDev())
            throw new NotFoundHttpException('Wrong feedback ID');

        /** @var FeedbackState $feedbackState */
        $feedbackState = FeedbackState::find()->where([
            'id'          => $stateId,
            'feedback_id' => $feedbackId,
        ])->one();

        if (!$feedbackState)
            throw new NotFoundHttpException('Wrong feedback state ID');

        if ($feedbackState->feedback_id != $feedbackId)
            throw new NotFoundHttpException('Wrong feedback ID');

        $feedback->setActiveState($feedbackState);
        $feedback->clearFictive();

        $inputFieldTemplates = InputFieldTemplate::find()->where([
            'input_field_template_reference' => $feedback->getInputFieldTemplateReference(),
            'active'                         => true,
        ])->orderBy([
            'input_field_order' => SORT_ASC
        ])->all();

        /** @var InputFilesBlock[] $inputFilesTemplates */
        $inputFilesTemplates = InputFilesBlock::find()->where([
            'input_file_template_reference' => $feedback->getInputFileTemplateReference(),
            'active'                        => true
        ])->orderBy([
            'input_file_order' => SORT_ASC
        ])->all();

        foreach($inputFilesTemplates as $inputFilesTemplate)
            $inputFilesTemplate->currentInputFileReference = $feedbackState->input_files_reference;

        /** @var InputImagesBlock[] $inputImagesTemplates */
        $inputImagesTemplates = InputImagesBlock::find()->where([
            'input_image_template_reference' => $feedback->getInputImageTemplateReference(),
            'active'                         => true,
        ])->orderBy([
            'input_image_order' => SORT_ASC
        ])->all();

        foreach($inputImagesTemplates as $inputImagesTemplate)
            $inputImagesTemplate->currentInputImageReference = $feedbackState->input_images_reference;

        $inputConditionsTemplates = InputConditionTemplate::find()->where([
            'input_condition_template_reference' => $feedback->getInputConditionTemplateReference(),
            'active'                             => true,
        ])->orderBy([
            'input_condition_order' => SORT_ASC
        ])->all();

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/admin/state-view',[
            'feedback'                 => $feedback,
            'feedbackState'            => $feedbackState,
            'inputFieldTemplates'      => $inputFieldTemplates,
            'inputFilesTemplates'      => $inputFilesTemplates,
            'inputImagesTemplates'     => $inputImagesTemplates,
            'inputConditionsTemplates' => $inputConditionsTemplates,
        ]);
    }

    /**
     * Change stage viewed state
     * @param $feedbackId
     * @param $stateId
     * @return \yii\web\Response
     * @throws FeedbackException
     * @throws NotFoundHttpException
     */
    public function actionChangeStateViewed($feedbackId, $stateId)
    {
        $feedback = Feedback::getInstanceById($feedbackId);

        if (!$feedback) throw new NotFoundHttpException('Wrong feedback ID');

        if ($feedback->active == false && !CommonModule::isUnderDev())
            throw new NotFoundHttpException('Wrong feedback ID');

        /** @var FeedbackState $feedbackState */
        $feedbackState = FeedbackState::find()->where([
            'id'          => $stateId,
            'feedback_id' => $feedbackId,
        ])->one();

        if (!$feedbackState) throw new NotFoundHttpException('Wrong feedback state ID');

        if ($feedbackState->isViewed())
            $feedbackState->markAsNoneViewed();
        else
            $feedbackState->markAsViewed();

        return $this->redirect(Url::toRoute([
            '/feedback/admin/view-state',
            'feedbackId' => $feedback->id,
            'stateId'    => $feedbackState->id,
            ])
        );
    }

    /**
     * Delete state
     * @param $feedbackId
     * @param $stateId
     * @return \yii\web\Response
     * @throws FeedbackException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDeleteState($feedbackId, $stateId)
    {
        $feedback = Feedback::getInstanceById($feedbackId);

        if (!$feedback) throw new NotFoundHttpException('Wrong feedback ID');

        if ($feedback->active == false && !CommonModule::isUnderDev())
            throw new NotFoundHttpException('Wrong feedback ID');

        /** @var FeedbackState $feedbackState */
        $feedbackState = FeedbackState::find()->where([
            'id'          => $stateId,
            'feedback_id' => $feedbackId,
        ])->one();

        if (!$feedbackState) throw new NotFoundHttpException('Wrong feedback state ID');

        $feedbackState->setFeedback($feedback);

        if ($feedbackState->delete())
            return $this->redirect(Url::toRoute(['/feedback/admin/elements-list', 'feedbackId' => $feedbackId]));

        throw new FeedbackException('Can`t delete state');
    }

    /**
     * Action for edit
     * @param $feedbackId
     * @return string
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \Iliich246\YicmsFeedback\Base\FeedbackException
     */
    public function actionFeedbackPages($feedbackId)
    {
        $feedback = Feedback::getInstanceById($feedbackId);

        if (!$feedback) throw new NotFoundHttpException('Wrong feedback ID');

        if ($feedback->active == false && !CommonModule::isUnderDev())
            throw new NotFoundHttpException('Wrong feedback ID');

        if (!$feedback->admin_can_edit_fields && !CommonModule::isUnderDev())
            throw new NotFoundHttpException('Wrong feedback ID');
        
        $feedback->clearFictive();

        $fieldsGroup = new FieldsGroup();
        $fieldsGroup->setFieldsReferenceAble($feedback);
        $fieldsGroup->initialize();

        //try to load validate and save field via pjax
        if ($fieldsGroup->load(Yii::$app->request->post()) && $fieldsGroup->validate()) {

            if (!$fieldsGroup->save()) {
                //TODO: bootbox error
            }

            return $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/pjax/fields', [
                'fieldsGroup'            => $fieldsGroup,
                'fieldTemplateReference' => $feedback->getFieldTemplateReference(),
                'success'                => true,
            ]);
        }

        $conditionsGroup = new ConditionsGroup();
        $conditionsGroup->setConditionsReferenceAble($feedback);
        $conditionsGroup->initialize();

        if ($conditionsGroup->load(Yii::$app->request->post()) && $conditionsGroup->validate()) {
            $conditionsGroup->save();

            return $this->render(CommonModule::getInstance()->yicmsLocation  . '/Common/Views/conditions/conditions', [
                'conditionsGroup'            => $conditionsGroup,
                'conditionTemplateReference' => $feedback->getConditionTemplateReference(),
                'success'                    => true,
            ]);
        }

        /** @var FilesBlock $filesBlocks */
        $filesBlocksQuery = FilesBlock::find()->where([
            'file_template_reference' => $feedback->getFileTemplateReference(),
        ])->orderBy([
            FilesBlock::getOrderFieldName() => SORT_ASC
        ]);

        if (CommonModule::isUnderAdmin())
            $filesBlocksQuery->andWhere([
                'editable' => true,
            ]);

        $filesBlocks = $filesBlocksQuery->all();

        foreach ($filesBlocks as $fileBlock)
            $fileBlock->setFileReference($feedback->getFileReference());

        /** @var ImagesBlock $imagesBlock */
        $imagesBlockQuery = ImagesBlock::find()->where([
            'image_template_reference' => $feedback->getImageTemplateReference()
        ])->orderBy([
            ImagesBlock::getOrderFieldName() => SORT_ASC
        ]);

        if (CommonModule::isUnderAdmin())
            $imagesBlockQuery->andWhere([
                'editable' => true,
            ]);

        $imagesBlocks = $imagesBlockQuery->all();

        foreach ($imagesBlocks as $imagesBlock)
            $imagesBlock->setImageReference($feedback->getImageReference());

        return $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/admin/feedback-pages',[
            'feedback'        => $feedback,
            'fieldsGroup'     => $fieldsGroup,
            'filesBlocks'     => $filesBlocks,
            'imagesBlocks'    => $imagesBlocks,
            'conditionsGroup' => $conditionsGroup
        ]);
    }

    /**
     * Action for upload file uploading
     * @param $inputFileBlockId
     * @param $inputFileId     *
     * @return bool|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \yii\base\ExitException
     */
    public function actionUploadInputFile($inputFileBlockId, $inputFileId)
    {
        /** @var InputFile $inputFile */
        $inputFile = InputFile::findOne($inputFileId);

        if (!$inputFile) throw new NotFoundHttpException('Wrong input file id = ' . $inputFileId);

        if ($inputFile->feedback_input_files_template_id != $inputFileBlockId)
            throw new NotFoundHttpException('Wrong input file block id');

        $inputFileBlock = InputFilesBlock::getInstanceById($inputFileBlockId);

        if (!$inputFileBlock)
            throw new NotFoundHttpException('Wrong input file block in file');

        $path = $inputFile->getPath();

        if (!$path) return $this->goBack();

        $fileName = $inputFile->getFileName();

        Yii::$app->response->sendFile($path, $fileName)->send();
        Yii::$app->end();

        return false;
    }
}
