<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\base\Model;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Fields\Field;
use Iliich246\YicmsCommon\Fields\FieldsHandler;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\FieldsInterface;
use Iliich246\YicmsCommon\Fields\FieldReferenceInterface;
use Iliich246\YicmsCommon\Files\File;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Files\FilesHandler;
use Iliich246\YicmsCommon\Files\FilesInterface;
use Iliich246\YicmsCommon\Files\FilesReferenceInterface;
use Iliich246\YicmsCommon\Images\Image;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Images\ImagesHandler;
use Iliich246\YicmsCommon\Images\ImagesInterface;
use Iliich246\YicmsCommon\Images\ImagesReferenceInterface;
use Iliich246\YicmsCommon\Conditions\Condition;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsCommon\Conditions\ConditionsHandler;
use Iliich246\YicmsCommon\Conditions\ConditionsInterface;
use Iliich246\YicmsCommon\Conditions\ConditionsReferenceInterface;
use Iliich246\YicmsFeedback\InputFields\InputField;
use Iliich246\YicmsFeedback\InputFields\InputFieldsStates;
use Iliich246\YicmsFeedback\InputFields\FieldsInputHandler;
use Iliich246\YicmsFeedback\InputFields\FieldInputInterface;
use Iliich246\YicmsFeedback\InputFields\FieldInputReferenceInterface;
use Iliich246\YicmsFeedback\InputFiles\FileInputInterface;
use Iliich246\YicmsFeedback\InputFiles\FileInputReferenceInterface;
use Iliich246\YicmsFeedback\InputImages\ImageInputInterface;
use Iliich246\YicmsFeedback\InputImages\ImageInputReferenceInterface;
use Iliich246\YicmsFeedback\InputConditions\ConditionsInputInterface;
use Iliich246\YicmsFeedback\InputConditions\ConditionsInputReferenceInterface;

/**
 * Class FeedbackStages
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property string $program_name
 * @property integer $stage_order
 * @property integer $editable
 * @property integer $visible
 * @property string $stage_field_template_reference
 * @property string $stage_file_template_reference
 * @property string $stage_image_template_reference
 * @property string $stage_condition_template_reference
 * @property string $stage_field_reference
 * @property string $stage_file_reference
 * @property string $stage_image_reference
 * @property string $stage_condition_reference
 * @property string $input_field_template_reference
 * @property string $input_file_template_reference
 * @property string $input_image_template_reference
 * @property string $input_condition_template_reference
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackStages extends ActiveRecord implements
    FieldsInterface,
    FieldReferenceInterface,
    FilesInterface,
    FilesReferenceInterface,
    ImagesInterface,
    ImagesReferenceInterface,
    ConditionsReferenceInterface,
    ConditionsInterface,
    FieldInputReferenceInterface,
    FieldInputInterface,
    FileInputReferenceInterface,
    FileInputInterface,
    ImageInputReferenceInterface,
    ImageInputInterface,
    ConditionsInputReferenceInterface,
    ConditionsInputInterface,
    FictiveInterface,
    SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var FieldsHandler instance of field handler object */
    private $fieldHandler;
    /** @var FilesHandler instance of file handler object */
    private $fileHandler;
    /** @var ImagesHandler instance of image handler object */
    private $imageHandler;
    /** @var ConditionsHandler instance of condition handler object */
    private $conditionHandler;
    /** @var FieldsHandler instance of input field handler object */
    private $fieldInputHandler;
    /** @var FilesHandler instance of input file handler object */
    private $fileInputHandler;
    /** @var ImagesHandler instance of input image handler object */
    private $imageInputHandler;
    /** @var ConditionsHandler instance of input condition handler object */
    private $conditionInputHandler;
    /** @var Feedback instance */
    private $feedback = null;

    /** @var FieldTemplate[] instances of input fields templates  */
    private $inputFieldTemplates;
    /** @var  InputField[] array of input fields */
    public $inputFields;

    //experimental features
    /** @var null|FeedbackState active state of this stage */
    private $activeState = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_stages}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->visible  = true;
        $this->editable = true;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'program_name' => 'Program Name',
            'editable'     => 'Editable',
            'visible'      => 'Visible',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'program_name', 'required', 'message' => 'Obligatory input field'
            ],
            [
                'program_name', 'string', 'max' => '50', 'tooLong' => 'Program name must be less than 50 symbols'
            ],
            [
                'program_name', 'validateProgramName'
            ],
            [
                ['stage_order'],
                'integer'
            ],
            [
                [
                    'editable',
                    'visible',
                ],
                'boolean'
            ],
            [
                [
                    'stage_field_template_reference',
                    'stage_file_template_reference',
                    'stage_image_template_reference',
                    'stage_condition_template_reference',
                    'stage_field_reference',
                    'stage_file_reference',
                    'stage_image_reference',
                    'stage_condition_reference',
                    'input_field_template_reference',
                    'input_file_template_reference',
                    'input_image_template_reference',
                    'input_condition_template_reference',
                ],
                'string'
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name', 'editable', 'visible',
            ],
            self::SCENARIO_UPDATE => [
                'program_name',  'editable', 'visible',
            ],
        ];
    }

    /**
     * Validates the program name.
     * @param $attribute
     * @param $params
     * @throws FeedbackException
     */
    public function validateProgramName($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $stagesQuery = self::find()->where([
                'feedback_id'  => $this->getFeedback()->id,
                'program_name' => $this->program_name
            ]);

            if ($this->scenario == self::SCENARIO_UPDATE)
                $stagesQuery->andWhere(['not in', 'program_name', $this->getOldAttribute('program_name')]);

            $stages = $stagesQuery->count();
            if ($stages)$this->addError($attribute, 'Feedback with same name already exist in system');
        }
    }

    /**
     * Creates new feedback stage with all service records
     * @return bool
     * @throws FeedbackException
     */
    public function create()
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->stage_order = $this->maxOrder();
            $this->feedback_id = $this->getFeedback()->id;
        }

        if (!$this->save(false))
            throw new FeedbackException('Can not create feedback stage '. $this->program_name);

        return true;
    }

    public function isConstraints()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        return true;
    }

    /**
     * @return bool
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function initialize()
    {
        $fieldTemplatesQuery = FieldTemplate::getListQuery($this->getInputFieldTemplateReference());

        $fieldTemplatesQuery->andWhere([
            'visible' => true,
            'language_type' => FieldTemplate::LANGUAGE_TYPE_SINGLE
        ])->orderBy([
            FieldTemplate::getOrderFieldName() => SORT_ASC
        ])->orderBy('id');

        $this->inputFieldTemplates = $fieldTemplatesQuery->all();

        foreach($this->inputFieldTemplates as $inputFieldTemplate) {
            if (!$this->isActiveState()) {
                $inputField = $this->getInputField($inputFieldTemplate->program_name);
            } else {
                //TODO:
                $inputField = null;
            }

            $this->inputFields["$inputFieldTemplate->id"] = $inputField;
        }

        return true;
    }

    /**
     * Reorder method load method for give user clear api
     * @param $data
     * @param null $formName
     * @return bool
     */
    public function loadDev($data, $formName = null)
    {
        return parent::load($data, $formName);
    }

    /**
     * Reorder method validate method for give user clear api
     * @param null $attributeNames
     * @param bool|true $clearErrors
     * @return bool
     */
    public function validateDev($attributeNames = null, $clearErrors = true)
    {
        return parent::validate($attributeNames, $clearErrors);
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        return Model::loadMultiple($this->inputFields, $data);
    }

    /**
     * @param null $attributeNames
     * @param bool|true $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        return Model::validateMultiple($this->inputFields);
    }

    /**
     * @return bool
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function handle()
    {
        if (!$this->isActiveState()) {

            $state = new FeedbackState();
            $state->stage_id = $this->id;
            $state->input_fields_reference = Field::generateReference();

            $state->save(false);

            foreach ($this->inputFields as $templateId => $inputField) {
                $fieldState = new InputFieldsStates();
                $fieldState->state_id = $state->id;
                $fieldState->common_fields_template_id = $templateId;

                $fieldState->save();

                $inputField->common_fields_template_id = $templateId;
                $inputField->field_reference = $state->input_fields_reference;

                $inputField->save();
            }

            //TODO: for files, images, conditions do the same
        } else {

        }

        return true;
    }

    /**
     * Return associated feedback db instance
     * @return Feedback|null
     * @throws FeedbackException
     */
    public function getFeedback()
    {
        if (!is_null($this->feedback))
            return $this->feedback;

        return $this->feedback = Feedback::getInstance($this->feedback_id);
    }

    /**
     * Feedback setter
     * @param Feedback $feedback
     */
    public function setFeedback(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @return bool
     */
    public function isActiveState()
    {
        if (is_null($this->activeState)) return false;

        return true;
    }

    /**
     * @param $id
     * @return bool|FeedbackState|null
     */
    public function setActiveState($id)
    {
        $activeState = FeedbackState::findOne($id);

        if (!$activeState) return false;

        return $this->activeState = $activeState;
    }

    /**
     *
     */
    public function dropActiveState()
    {
        $this->activeState = null;
    }

    /**
     * @inheritdoc
     */
    public function getFieldHandler()
    {
        if (!$this->fieldHandler)
            $this->fieldHandler = new FieldsHandler($this);

        return $this->fieldHandler;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getField($name)
    {
        return $this->getFieldHandler()->getField($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldTemplateReference()
    {
        if (!$this->stage_field_template_reference) {
            $this->stage_field_template_reference = FieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_field_template_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldReference()
    {
        if (!$this->stage_field_reference) {
            $this->stage_field_reference = Field::generateReference();
            $this->save(false);
        }

        return $this->stage_field_reference;
    }

    /**
     * @inheritdoc
     */
    public function getFileHandler()
    {
        if (!$this->fileHandler)
            $this->fileHandler = new FilesHandler($this);

        return $this->fileHandler;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileReference()
    {
        if (!$this->stage_file_reference) {
            $this->stage_file_reference = File::generateReference();
            $this->save(false);
        }

        return $this->stage_file_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileTemplateReference()
    {
        if (!$this->stage_file_template_reference) {
            $this->stage_file_template_reference = FilesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_file_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getFileBlock($name)
    {
        return $this->getFileHandler()->getFileBlock($name);
    }

    /**
     * @inheritdoc
     */
    public function getImagesHandler()
    {
        if (!$this->imageHandler)
            $this->imageHandler = new ImagesHandler($this);

        return $this->imageHandler;
    }

    /**
     * @inheritdoc
     */
    public function getImageBlock($name)
    {
        return $this->getImagesHandler()->getImageBlock($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageTemplateReference()
    {
        if (!$this->stage_image_template_reference) {
            $this->stage_image_template_reference = ImagesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_image_template_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageReference()
    {
        if (!$this->stage_image_reference) {
            $this->stage_image_reference = Image::generateReference();
            $this->save(false);
        }

        return $this->stage_image_reference;
    }

    /**
     * @inheritdoc
     */
    public function getConditionsHandler()
    {
        if (!$this->conditionHandler)
            $this->conditionHandler = new ConditionsHandler($this);

        return $this->conditionHandler;
    }

    /**
     * @inheritdoc
     */
    public function getCondition($name)
    {
        return $this->getConditionsHandler()->getCondition($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionTemplateReference()
    {
        if (!$this->stage_condition_template_reference) {
            $this->stage_condition_template_reference = ConditionTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_condition_template_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionReference()
    {
        if (!$this->stage_condition_reference) {
            $this->stage_condition_reference = Condition::generateReference();
            $this->save(false);
        }

        return $this->stage_condition_reference;
    }

    /**
     * @inheritdoc
     */
    public function getInputFieldHandler()
    {
        if (!$this->fieldInputHandler)
            $this->fieldInputHandler = new FieldsInputHandler($this);

        return $this->fieldInputHandler;
    }

    /**
     * @inheritdoc
     */
    public function getInputField($name)
    {
//        if ($this->isFictive()) {
//            $fictiveField = new InputField();
//            $fictiveField->setFictive();
//
//            /** @var FieldTemplate $template */
//            $template = FieldTemplate::getInstance($this->input_field_template_reference, $name);
//            $fictiveField->setTemplate($template);
//
//            return $fictiveField;
//        }

        return $this->getInputFieldHandler()->getInputField($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputFieldTemplateReference()
    {
        if (!$this->input_field_template_reference) {
            $this->input_field_template_reference = FieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->input_field_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getInputFieldReference()
    {
        //if active stage return stage reference else generate new
        return 'test_reference';
    }

    ////file
    /**
     * @inheritdoc
     */
    public function getInputFileHandler()
    {
        if (!$this->fileInputHandler)
            //$this->fileInputHandler = new FileIn($this);

        return $this->fileInputHandler;
    }

    /**
     * @inheritdoc
     */
    public function getInputFileBlock($name)
    {

    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputFileTemplateReference()
    {
        if (!$this->input_file_template_reference) {
            $this->input_file_template_reference = FilesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->input_file_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getInputFileReference()
    {
        //TODO:
    }

    /**
     * @inheritdoc
     */
    public function getInputImagesHandler()
    {

    }

    /**
     * @inheritdoc
     */
    public function getInputImageBlock($name)
    {

    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputImageTemplateReference()
    {
        if (!$this->input_image_template_reference) {
            $this->input_image_template_reference = ImagesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->input_image_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getInputImageReference()
    {
        // TODO: Implement getInputImageReference() method.
    }

    /**
     * @inheritdoc
     */
    public function getInputConditionsHandler()
    {
        // TODO: Implement getInputConditionsHandler() method.
    }

    /**
     * @inheritdoc
     */
    public function getInputCondition($name)
    {
        // TODO: Implement getInputCondition() method.
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputConditionTemplateReference()
    {
        if (!$this->input_condition_template_reference) {
            $this->input_condition_template_reference = ConditionTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->input_condition_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getInputConditionReference()
    {
        // TODO: Implement getInputConditionReference() method.
    }

    /**
     * @inheritdoc
     */
    public function getConditionInputModel()
    {
        // TODO: Implement getConditionInputModel() method.
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'feedback_id'  => $this->getFeedback()->id
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'stage_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->stage_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->stage_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function configToChangeOfOrder()
    {
        $this->scenario = self::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function clearFictive()
    {

    }

    /**
     * @inheritdoc
     */
    public function isFictive()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setFictive()
    {

    }
}
