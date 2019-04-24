<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsFeedback\InputConditions\InputCondition;
use Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFiles\InputFile;
use Iliich246\YicmsFeedback\InputFiles\InputFilesBlock;
use Iliich246\YicmsFeedback\InputImages\InputImage;
use Iliich246\YicmsFeedback\InputImages\InputImagesBlock;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Iliich246\YicmsFeedback\InputFields\InputField;

/**
 * Class FeedbackState
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property string $input_fields_reference
 * @property string $input_files_reference
 * @property string $input_images_reference
 * @property string $input_conditions_reference
 * @property integer $is_handled
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackState extends ActiveRecord
{
    /** @var Feedback instance associated with this state */
    private $feedback;
    /** @var InputField[] for working with forms */
    public $inputFields;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_states}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Feedback getter
     * @return Feedback|null
     * @throws FeedbackException
     */
    public function getFeedback()
    {
        if (!$this->feedback) return $this->feedback;

        return $this->feedback = Feedback::getInstanceById($this->feedback_id);
    }

    /**
     * Feedback setter
     * @param Feedback $feedback
     * @throws FeedbackException
     */
    public function setFeedback(Feedback $feedback)
    {
        if ($this->feedback_id != $feedback->id)
            throw new FeedbackException('Try to set wrong feedback for this state');

        $this->feedback = $feedback;
    }

    /**
     * Return state name for admin panel
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function adminName()
    {
        return 'Message: ' . Yii::$app->formatter->asDatetime($this->created_at);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        /** @var InputFieldTemplate[] $inputFieldTemplates */
        $inputFieldTemplates = InputFieldTemplate::find()->where([
            'input_field_template_reference' => $this->getFeedback()->getInputFieldTemplateReference()
        ])->all();

        foreach ($inputFieldTemplates as $inputFieldTemplate) {
            /** @var InputField[] $inputFields */
            $inputFields = InputField::find()->where([
                'feedback_input_fields_template_id' => $inputFieldTemplate->id,
                'input_field_reference'             => $this->input_fields_reference,
            ])->all();

            foreach ($inputFields as $inputField)
                $inputField->delete();
        }

        /** @var InputFilesBlock[] $inputFilesBlocks */
        $inputFilesBlocks = InputFilesBlock::find()->where([
            'input_file_template_reference' => $this->getFeedback()->getInputFileTemplateReference()
        ])->all();

        foreach ($inputFilesBlocks as $inputFilesBlock) {
            /** @var InputFile[] $inputFiles */
            $inputFiles = InputFile::find()->where([
                'feedback_input_files_template_id' => $inputFilesBlock->id,
                'input_file_reference'             => $this->input_files_reference
            ])->all();

            foreach($inputFiles as $inputFile)
                $inputFile->delete();
        }

        /** @var InputImagesBlock[] $inputImagesBlocks */
        $inputImagesBlocks = InputImagesBlock::find()->where([
            'input_image_template_reference' => $this->getFeedback()->getInputImageTemplateReference()
        ])->all();

        foreach($inputImagesBlocks as $inputImagesBlock) {
            /** @var InputImage[] $inputImages */
            $inputImages = InputImage::find()->where([
                'feedback_input_images_template_id' => $inputImagesBlock->id,
                'input_image_reference'             => $this->input_images_reference,
            ])->all();

            foreach($inputImages as $inputImage)
                $inputImage->delete();
        }

        /** @var InputConditionTemplate[] $inputConditionTemplates */
        $inputConditionTemplates = InputConditionTemplate::find()->where([
            'input_condition_template_reference' => $this->getFeedback()->getInputConditionTemplateReference()
        ])->all();

        foreach($inputConditionTemplates as $inputConditionTemplate) {
            /** @var InputCondition[] $inputConditions */
            $inputConditions = InputCondition::find()->where([
                'input_condition_template_template_id' => $inputConditionTemplate->id,
                'input_condition_reference'            => $this->input_conditions_reference
            ])->all();

            foreach($inputConditions as $inputCondition)
                $inputCondition->delete();
        }

        return parent::delete();
    }

    /**
     * Return true if state viewed
     * @return bool
     */
    public function isViewed()
    {
        return !!$this->is_handled;
    }

    /**
     * Marks state as viewed
     * @return bool
     */
    public function markAsViewed()
    {
        $this->is_handled = true;
        return $this->save(false);
    }

    /**
     * Marks state as not viewed
     * @return bool
     */
    public function markAsNoneViewed()
    {
        $this->is_handled = false;
        return $this->save(false);
    }
}
