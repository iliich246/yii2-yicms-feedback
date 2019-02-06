<?php

namespace Iliich246\YicmsFeedback\Base;


use Iliich246\YicmsFeedback\InputFiles\FilesInputHandler;
use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
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
use Iliich246\YicmsFeedback\InputFields\InputFieldGroup;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
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
 * Class Feedback
 *
 * @property integer $id
 * @property string $program_name
 * @property integer $feedback_order
 * @property integer $type
 * @property bool $editable
 * @property bool $active
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
class Feedback extends ActiveRecord implements
    FictiveInterface,
    SortOrderInterface,
    NonexistentInterface,
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
    ConditionsInputInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var self[] buffer array */
    private static $feedbackBuffer = [];

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
    /** @var InputFieldGroup instance */
    public $inputFieldsGroup;
    /** @var bool keep nonexistent state of feedback */
    private $isNonexistent = false;
    /** @var string keeps name of nonexistent feedback */
    private $nonexistentName;
    /** @var bool keeps fictive state of this input feedback */
    private $isFictive = true;
    /** @var FeedbackState|null current state of this feedback */
    private $currentState = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->active   = true;
        $this->editable = true;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'program_name'   => 'Program name',
            'editable'       => 'Editable',
            'active'         => 'Active',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name', 'editable', 'active'
            ],
            self::SCENARIO_UPDATE => [
                'program_name', 'editable', 'active'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['program_name', 'required', 'message' => 'Obligatory input field'],
            ['program_name', 'string', 'max' => '50', 'tooLong' => 'Program name must be less than 50 symbols'],
            ['program_name', 'validateProgramName'],
            [['active', 'editable'], 'boolean']
        ];
    }

    /**
     * Validates the program name.
     * This method serves as the inline validation for page program name.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateProgramName($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $pagesQuery = self::find()->where(['program_name' => $this->program_name]);

            if ($this->scenario == self::SCENARIO_UPDATE)
                $pagesQuery->andWhere(['not in', 'program_name', $this->getOldAttribute('program_name')]);

            $pages = $pagesQuery->all();
            if ($pages)$this->addError($attribute, 'Page with same name already exist in system');
        }
    }

    /**
     * Return instance of page by her name
     * @param $programName
     * @return self
     * @throws FeedbackException
     */
    public static function getByName($programName)
    {
        foreach(self::$feedbackBuffer as $feedback)
            if ($feedback->program_name == $programName)
                return $feedback;

        /** @var self $feedback */
        $feedback = self::find()
            ->where(['program_name' => $programName])
            ->one();

        if ($feedback) {
            self::$feedbackBuffer[$feedback->id] = $feedback;
            return $feedback;
        }

        Yii::error("Сan not find feedback with name " . $programName, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException('Сan not find feedback with name ' . $programName);
        }

        $nonexistentFeedback = new self();
        $nonexistentFeedback->setNonexistent();
        $nonexistentFeedback->nonexistentName = $programName;

        return $nonexistentFeedback;
    }

    /**
     * Returns instance of feedback by her id
     * @param $id
     * @return Feedback|null
     * @throws FeedbackException
     */
    public static function getInstance($id)
    {
        if (isset(self::$feedbackBuffer[$id]))
            return self::$feedbackBuffer[$id];

        $feedback = self::findOne($id);

        if ($feedback) {
            self::$feedbackBuffer[$feedback->id] = $feedback;
            return $feedback;
        }

        Yii::error("Сan not find feedback with id " . $id, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException("Сan not find feedback with id " . $id);
        }

        $nonexistentFeedback = new self();
        $nonexistentFeedback->setNonexistent();

        return $nonexistentFeedback;
    }

    /**
     * Creates new feedback with all service records
     * @return bool
     * @throws FeedbackException
     */
    public function create()
    {
        if ($this->isNonexistent) return false;

        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->feedback_order = $this->maxOrder();
        }

        if (!$this->save(false))
            throw new FeedbackException('Can not create feedback'. $this->program_name);

        return true;
    }

    /**
     * @return bool
     */
    public function isConstraints()
    {
        if ($this->isNonexistent) return false;

        return true;
    }

    /**
     * Returns name of feedback
     * @param LanguagesDb|null $language
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name(LanguagesDb $language = null)
    {
        if ($this->isNonexistent) return false;

        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if (!FeedbackNamesTranslatesDb::getTranslate($this->id, $language->id)) return $this->program_name;

        return FeedbackNamesTranslatesDb::getTranslate($this->id, $language->id)->name;
    }

    /**
     * Returns description of feedback
     * @param LanguagesDb|null $language
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function description(LanguagesDb $language = null)
    {
        if ($this->isNonexistent) return false;

        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if (!FeedbackNamesTranslatesDb::getTranslate($this->id, $language->id)) return $this->program_name;

        return FeedbackNamesTranslatesDb::getTranslate($this->id, $language->id)->description;

    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->isNonexistent) return false;

        $feedbackNames = FeedbackNamesTranslatesDb::find()->where([
            'feedback_id' => $this->id,
        ])->all();

        foreach($feedbackNames as $feedbackName)
            $feedbackName->delete();

        return parent::delete();
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
     * @return bool
     */
    public function validateDev()
    {
        return parent::validate();
    }

    /**
     * Proxy initialize method to active stage
     */
    public function initialize()
    {
        $this->inputFieldsGroup = new InputFieldGroup();
        $this->inputFieldsGroup->setFieldInputReference($this);
        return $this->inputFieldsGroup->initialize();

        //TODO: make with other input objects
    }

    /**
     * Proxy load method to active stage
     * @param array $data
     * @param null $formName
     * @return bool|void
     */
    public function load($data, $formName = null)
    {
        return $this->inputFieldsGroup->load($data);
    }

    /**
     * Proxy validate method to active stage
     * @param null $attributeNames
     * @param bool|true $clearErrors
     * @return bool|void
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        return $this->inputFieldsGroup->validate();
    }

    /**
     * @param bool|true $runValidation
     * @param null $attributeNames
     * @throws FeedbackException
     */
    public function handle($runValidation = true, $attributeNames = null)
    {
        $this->currentState = new FeedbackState();
        $this->currentState->feedback_id = $this->id;
        $this->currentState->save(false);

        $this->inputFieldsGroup->save();

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
    public function configToChangeOfOrder()
    {
        $this->scenario = self::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->feedback_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->feedback_order;
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'feedback_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find();
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
        return $this->getInputFieldHandler()->getInputField($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputFieldTemplateReference()
    {
        if (!$this->input_field_template_reference) {
            $this->input_field_template_reference = InputFieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->input_field_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getInputFieldReference()
    {
        if (!$this->currentState) return false;//TODO: may be exception???

        if (!$this->currentState->input_fields_reference) {
            $this->currentState->input_fields_reference = InputField::generateReference();
            $this->currentState->save(false);
        }

        return $this->currentState->input_fields_reference;
    }

    ////file
    /**
     * @inheritdoc
     */
    public function getInputFileHandler()
    {
        if (!$this->fileInputHandler)
            $this->fileInputHandler = new FilesInputHandler($this);

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
     */
    public function isNonexistent()
    {
        return $this->isNonexistent;
    }

    /**
     * @inheritdoc
     */
    public function setNonexistent()
    {
        $this->isNonexistent = true;
    }

    /**
     * @inheritdoc
     */
    public function getNonexistentName()
    {
        return $this->nonexistentName;
    }

    /**
     * @inheritdoc
     */
    public function setNonexistentName($name)
    {
        $this->nonexistentName = $name;
    }

    /**
     * @inheritdoc
     */
    public function setFictive()
    {
        $this->isFictive = true;
    }

    /**
     * @inheritdoc
     */
    public function clearFictive()
    {
        $this->isFictive = false;
    }

    /**
     * @inheritdoc
     */
    public function isFictive()
    {
        return $this->isFictive;
    }
}
