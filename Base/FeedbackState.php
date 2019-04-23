<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFiles\InputFile;
use Iliich246\YicmsFeedback\InputFiles\InputFilesBlock;
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

        /** @var InputFilesBlock[] $inputFilesTemplates */
        $inputFilesTemplates = InputFilesBlock::find()->where([
            'input_file_template_reference' => $this->getFeedback()->getInputFileTemplateReference()
        ])->all();

        foreach ($inputFilesTemplates as $inputFilesBlock) {
            $inputFiles = InputFile::find()->where([

            ])->all();
        }


        return parent::delete();
    }

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
}
