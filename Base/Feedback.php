<?php

namespace Iliich246\YicmsFeedback\Base;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Annotations\Annotator;
use Iliich246\YicmsCommon\Annotations\AnnotateInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
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
use Iliich246\YicmsFeedback\FeedbackModule;
use Iliich246\YicmsFeedback\InputFields\InputField;
use Iliich246\YicmsFeedback\InputFields\InputFieldGroup;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFields\FieldsInputHandler;
use Iliich246\YicmsFeedback\InputFields\FieldInputInterface;
use Iliich246\YicmsFeedback\InputFields\FieldInputReferenceInterface;
use Iliich246\YicmsFeedback\InputFiles\InputFile;
use Iliich246\YicmsFeedback\InputFiles\InputFilesBlock;
use Iliich246\YicmsFeedback\InputFiles\InputFilesGroup;
use Iliich246\YicmsFeedback\InputFiles\FilesInputHandler;
use Iliich246\YicmsFeedback\InputFiles\FileInputInterface;
use Iliich246\YicmsFeedback\InputFiles\FileInputReferenceInterface;
use Iliich246\YicmsFeedback\InputImages\InputImage;
use Iliich246\YicmsFeedback\InputImages\InputImagesBlock;
use Iliich246\YicmsFeedback\InputImages\InputImagesGroup;
use Iliich246\YicmsFeedback\InputImages\ImagesInputHandler;
use Iliich246\YicmsFeedback\InputImages\ImageInputInterface;
use Iliich246\YicmsFeedback\InputImages\ImageInputReferenceInterface;
use Iliich246\YicmsFeedback\InputConditions\InputCondition;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsGroup;
use Iliich246\YicmsFeedback\InputConditions\ConditionsInputHandler;
use Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate;
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
 * @property bool $admin_can_edit_fields
 * @property bool $admin_can_delete_states
 * @property integer $count_states_on_page
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
    ConditionsInputInterface,
    AnnotateInterface,
    AnnotatorFileInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    const EVENT_BEFORE_HANDLE = 'beforeHandle';

    const EVENT_AFTER_HANDLE = 'afterHandle';

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
    /** @var FieldsInputHandler instance of input field handler object */
    private $fieldInputHandler;
    /** @var FilesInputHandler instance of input file handler object */
    private $fileInputHandler;
    /** @var ImagesInputHandler instance of input image handler object */
    private $imageInputHandler;
    /** @var ConditionsInputHandler instance of input condition handler object */
    private $conditionInputHandler;
    /** @var InputFieldGroup instance */
    public $inputFieldsGroup;
    /** @var InputFilesGroup instance */
    public $inputFilesGroup;
    /** @var InputImagesGroup instance */
    public $inputImagesGroup;
    /** @var InputConditionsGroup instance */
    public $inputConditionsGroup;
    /** @var bool keep nonexistent state of feedback */
    private $isNonexistent = false;
    /** @var string keeps name of nonexistent feedback */
    private $nonexistentName;
    /** @var bool keeps fictive state of this input feedback */
    private $isFictive = true;
    /** @var FeedbackState|null current state of this feedback */
    private $currentState = null;
    /** @var null|integer count of states for this feedback  */
    private $countStates = null;
    /** @var null|integer count of new states of this feedback  */
    private $countNewStates = null;
    /** @var bool state of annotation necessity */
    private $needToAnnotate = true;
    /** @var Annotator instance */
    private $annotator = null;
    /** @var array of exception words for magical getter/setter */
    protected static $annotationExceptionWords = [
        'id',
        'isNewRecord',
        'scenario',
        'program_name',
        'feedback_order',
        'type',
        'editable',
        'active',
        'admin_can_edit_fields',
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
    ];

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
        $this->active                  = true;
        $this->editable                = true;
        $this->admin_can_edit_fields   = false;
        $this->admin_can_delete_states = true;
        $this->count_states_on_page    = 50;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'program_name'            => 'Program name',
            'editable'                => 'Editable',
            'active'                  => 'Active',
            'admin_can_edit_fields'   => 'Admin can edit page fields',
            'admin_can_delete_states' => 'Admin can delete states',
            'count_states_on_page'    => 'Count states on feedback page'
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name',
                'editable',
                'active',
                'admin_can_edit_fields',
                'admin_can_delete_states',
                'count_states_on_page'
            ],
            self::SCENARIO_UPDATE => [
                'program_name',
                'editable',
                'active',
                'admin_can_edit_fields',
                'admin_can_delete_states',
                'count_states_on_page'
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
            [['active', 'editable', 'admin_can_edit_fields', 'admin_can_delete_states'], 'boolean'],
            ['count_states_on_page', 'integer'],
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
     * @return null|Feedback
     * @throws FeedbackException
     */
    public static function getInstanceById($id)
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
     * Magical get method for use object annotations
     * @param string $name
     * @return bool|Condition|FilesBlock|ImagesBlock|mixed|object
     */
    public function __get($name)
    {
        if (in_array($name, self::$annotationExceptionWords))
            return parent::__get($name);

        if (strpos($name, 'input_field_' === 0)) {
            if ($this->isInputField(substr($name, 12)))
                return $this->getInputFieldHandler()->getInputField(substr($name, 12));
        }

        if (strpos($name, 'input_file_') === 0) {
            if ($this->isInputFileBlock(substr($name, 11)))
                return $this->getInputFileHandler()->getInputFileBlock(substr($name, 11));
        }

        if (strpos($name, 'input_image_') === 0) {
            if ($this->isInputImageBlock(substr($name, 12)))
                return $this->getInputImagesHandler()->getInputImageBlock(substr($name, 12));
        }

        if (strpos($name, 'input_condition_') === 0) {
            if ($this->isInputCondition(substr($name, 16)))
                return $this->getInputConditionsHandler()->getInputCondition(substr($name, 16));
        }

        if (strpos($name, 'input_') === 0) {
            $realName = substr($name, 6);

            if ($this->isInputField($realName))
                return $this->getInputFieldHandler()->getInputField($realName);

            if ($this->isInputFileBlock($realName))
                return $this->getInputFileHandler()->getInputFileBlock($realName);

            if ($this->isInputImageBlock($realName))
                return $this->getInputImagesHandler()->getInputImageBlock($realName);

            if ($this->isInputCondition($realName))
                return $this->getInputConditionsHandler()->getInputCondition($realName);
        }

        if (strpos($name, 'field_') === 0) {
            if ($this->isField(substr($name, 6)))
                return $this->getFieldHandler()->getField(substr($name, 6));

            return parent::__get($name);
        }

        if (strpos($name, 'file_') === 0) {
            if ($this->isFileBlock(substr($name, 5)))
                return $this->getFileHandler()->getFileBlock(substr($name, 5));

            return parent::__get($name);
        }

        if (strpos($name, 'image_') === 0) {
            if ($this->isImageBlock(substr($name, 6)))
                return $this->getImagesHandler()->getImageBlock(substr($name, 6));

            return parent::__get($name);
        }

        if (strpos($name, 'condition_') === 0) {
            if ($this->isCondition(substr($name, 10)))
                return $this->getConditionsHandler()->getCondition(substr($name, 10));

            return parent::__get($name);
        }

        if ($this->getFieldHandler()->isField($name))
            return $this->getFieldHandler()->getField($name);

        if ($this->getFileHandler()->isFileBlock($name))
            return $this->getFileHandler()->getFileBlock($name);

        if ($this->getImagesHandler()->isImageBlock($name))
            return $this->getImagesHandler()->getImageBlock($name);

        if ($this->getConditionsHandler()->isCondition($name))
            return $this->getConditionsHandler()->getCondition($name);

        return parent::__get($name);
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

        /** @var FeedbackState[] $feedbackStates */
        $feedbackStates = FeedbackState::find()->where([
            'feedback_id' => $this->id,
        ])->all();

        foreach ($feedbackStates as $feedbackState)
            $feedbackState->delete();

        /** @var InputFieldTemplate[] $inputFieldTemplates */
        $inputFieldTemplates = InputFieldTemplate::find()->where([
            'input_field_template_reference' => $this->getInputFieldTemplateReference()
        ])->all();

        foreach($inputFieldTemplates as $inputFieldTemplate)
            $inputFieldTemplate->delete();

        /** @var InputFilesBlock[] $inputFilesBlocks */
        $inputFilesBlocks = InputFilesBlock::find()->where([
            'input_file_template_reference' => $this->getInputFileTemplateReference()
        ])->all();

        foreach ($inputFilesBlocks as $inputFilesBlock)
            $inputFilesBlock->delete();

        /** @var InputImagesBlock[] $inputImagesBlocks */
        $inputImagesBlocks = InputImagesBlock::find()->where([
            'input_image_template_reference' => $this->getInputImageTemplateReference()
        ])->all();

        foreach($inputImagesBlocks as $inputImagesBlock)
            $inputImagesBlock->delete();

        /** @var InputConditionTemplate[] $inputConditionTemplates */
        $inputConditionTemplates = InputConditionTemplate::find()->where([
            'input_condition_template_reference' => $this->getInputConditionTemplateReference()
        ])->all();

        foreach($inputConditionTemplates as $inputConditionTemplate)
            $inputConditionTemplate->delete();

        /** @var FieldTemplate[] $fieldTemplates */
        $fieldTemplates = FieldTemplate::find()->where([
            'field_template_reference' => $this->getFieldTemplateReference(),
        ])->all();

        foreach($fieldTemplates as $fieldTemplate)
            $fieldTemplate->delete();

        /** @var FilesBlock[] $filesBlocks */
        $filesBlocks = FilesBlock::find()->where([
            'file_template_reference' => $this->getFileTemplateReference(),
        ])->all();

        foreach($filesBlocks as $fileBlock)
            $fileBlock->delete();

        /** @var ImagesBlock[] $imageBlocks */
        $imageBlocks = ImagesBlock::find()->where([
            'image_template_reference' => $this->getImageTemplateReference(),
        ])->all();

        foreach($imageBlocks as $imageBlock)
            $imageBlock->delete();

        /** @var ConditionTemplate[] $conditionTemplates */
        $conditionTemplates = ConditionTemplate::find()->where([
            'condition_template_reference' => $this->getConditionTemplateReference(),
        ])->all();

        foreach($conditionTemplates as $conditionTemplate)
            $conditionTemplate->delete();

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
     * Return count of states for this feedback
     * @return int|null|string
     */
    public function countStates()
    {
        if ($this->isNonexistent) return 0;

        if (!is_null($this->countStates)) return $this->countStates;

        return $this->countStates = FeedbackState::find()->where([
            'feedback_id' => $this->id,
        ])->count();
    }

    /**
     * Return true if feedback has new states
     * @return bool
     */
    public function isNewStates()
    {
        if ($this->isNonexistent) return false;

        return !!$this->countNewStates();
    }

    /**
     * Return count of new states of this feedback
     * @return int|null|string
     */
    public function countNewStates()
    {
        if ($this->isNonexistent) return 0;

        if (!is_null($this->countNewStates)) return $this->countNewStates;

        return $this->countNewStates = FeedbackState::find()->where([
            'feedback_id' => $this->id,
            'is_handled'  => false
        ])->count();
    }

    /**
     * @return ActiveQuery
     */
    public function statesQuery()
    {
        return FeedbackState::find()->where([
            'feedback_id' => $this->id,
        ]);
    }

    /**
     * Initialize feedback
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function initialize()
    {
        $this->inputFieldsGroup = new InputFieldGroup();
        $this->inputFieldsGroup->setFieldInputReference($this);
        $this->inputFieldsGroup->initialize();

        $this->inputFilesGroup = new InputFilesGroup();
        $this->inputFilesGroup->setFileInputReference($this);
        $this->inputFilesGroup->initialize();

        $this->inputImagesGroup = new InputImagesGroup();
        $this->inputImagesGroup->setImageInputReference($this);
        $this->inputImagesGroup->initialize();

        $this->inputConditionsGroup = new InputConditionsGroup();
        $this->inputConditionsGroup->setConditionInputReference($this);
        $this->inputConditionsGroup->initialize();
    }

    /**
     * Handle load method for this feedback
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        if (!$this->inputFieldsGroup->isActiveInputFields() &&
            !$this->inputFilesGroup->isActiveInputFiles() &&
            !$this->inputImagesGroup->isActiveInputImages() &&
            !$this->inputConditionsGroup->isActiveInputConditions()
        ) return false;

        if (!$this->inputFieldsGroup->isActiveInputFields())
            $inputFieldsLoaded = true;
        else
            $inputFieldsLoaded = $this->inputFieldsGroup->load($data);

        if (!$this->inputFilesGroup->isActiveInputFiles())
            $inputFilesLoaded = true;
        else
            $inputFilesLoaded = $this->inputFilesGroup->load($data);

        if (!$this->inputImagesGroup->isActiveInputImages())
            $inputImagesLoaded = true;
        else
            $inputImagesLoaded = $this->inputImagesGroup->load($data);

        if (!$this->inputConditionsGroup->isActiveInputConditions())
            $inputConditionsLoaded = true;
        else
            $inputConditionsLoaded = $this->inputConditionsGroup->load($data);

//        if ($inputConditionsLoaded)
//            throw new \yii\base\Exception(print_r([
//                $inputFieldsLoaded,
//                $inputFilesLoaded,
//                $inputImagesLoaded,
//                $inputConditionsLoaded
//            ], true));

        if ($inputFieldsLoaded && $inputFilesLoaded && $inputImagesLoaded && $inputConditionsLoaded)
            return true;

        return false;
    }

    /**
     * Proxy validate method to active stage
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     * @throws FeedbackException
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!$this->inputFieldsGroup->isActiveInputFields() &&
            !$this->inputFilesGroup->isActiveInputFiles() &&
            !$this->inputImagesGroup->isActiveInputImages() &&
            !$this->inputConditionsGroup->isActiveInputConditions()
        ) return false;

        if (!$this->inputFieldsGroup->isActiveInputFields())
            $inputFieldsValidated = true;
        else
            $inputFieldsValidated = $this->inputFieldsGroup->validate();

        if (!$this->inputFilesGroup->isActiveInputFiles())
            $inputFilesValidated = true;
        else
            $inputFilesValidated = $this->inputFilesGroup->validate();

        if (!$this->inputImagesGroup->isActiveInputImages())
            $inputImagesValidated = true;
        else
            $inputImagesValidated = $this->inputImagesGroup->validate();

        if (!$this->inputConditionsGroup->isActiveInputConditions())
            $inputConditionsValidated = true;
        else
            $inputConditionsValidated = $this->inputConditionsGroup->validate();

//        throw new \yii\base\Exception(print_r([
//            $inputFieldsValidated,
//            $inputFilesValidated,
//            $inputImagesValidated,
//            $inputConditionsValidated
//
//        ], true));

        if ($inputFieldsValidated &&
            $inputFilesValidated &&
            $inputImagesValidated &&
            $inputConditionsValidated)
            return true;

        return false;
    }

    /**
     * Handle feedback form
     * @param bool|true $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function handle($runValidation = true, $attributeNames = null)
    {
        $this->trigger(self::EVENT_BEFORE_HANDLE);

        $this->currentState = new FeedbackState();
        $this->currentState->feedback_id = $this->id;
        $this->currentState->is_handled = false;
        $this->currentState->save(false);

        $inputFieldsSaved     = $this->inputFieldsGroup->save();
        $inputFilesSaved      = $this->inputFilesGroup->save();
        $inputImagesSaved     = $this->inputImagesGroup->save();
        $inputConditionsSaved = $this->inputConditionsGroup->save();

//        throw new \yii\base\Exception(print_r([
//            $inputFieldsSaved,
//            $inputFilesSaved,
//            $inputImagesSaved,
//            $inputConditionsSaved
//
//        ], true));

        $this->trigger(self::EVENT_AFTER_HANDLE);

        return true;
    }

    /**
     * This method clear feedback after handle it
     * @return void
     */
    public function clear()
    {
        $this->inputFieldsGroup->clear();
    }

    /**
     * FeedbackState getter
     * @return FeedbackState|null
     */
    public function getActiveState()
    {
        return $this->currentState;
    }

    /**
     * FeedbackState setter
     * @param FeedbackState $state
     */
    public function setActiveState(FeedbackState $state)
    {
        $this->currentState = $state;
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
     */
    public function isField($name)
    {
        return $this->getFieldHandler()->isField($name);
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
    public function isFileBlock($name)
    {
        $this->getFileHandler()->isFileBlock($name);
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
     */
    public function isImageBlock($name)
    {
        return $this->getImagesHandler()->isImageBlock($name);
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
     */
    public function isCondition($name)
    {
        return $this->getConditionsHandler()->isCondition($name);
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

    //////input field
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
     */
    public function isInputField($name)
    {
        return $this->getInputFieldHandler()->isInputField($name);
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
     * @throws FeedbackException
     */
    public function getInputFieldReference()
    {
        if (!$this->currentState)
            throw new FeedbackException('This method actual only for concrete feedback state');

        if (!$this->currentState->input_fields_reference) {
            $this->currentState->input_fields_reference = InputField::generateReference();
            $this->currentState->save(false);
        }

        return $this->currentState->input_fields_reference;
    }

    ////input file
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
        return $this->getInputFileHandler()->getInputFileBlock($name);
    }

    /**
     * @inheritdoc
     */
    public function isInputFileBlock($name)
    {
       return $this->getInputFileHandler()->isInputFileBlock($name);
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
     * @throws FeedbackException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputFileReference()
    {
        if (!$this->currentState)
            throw new FeedbackException('This method actual only for concrete feedback state');

        if (!$this->currentState->input_files_reference) {
            $this->currentState->input_files_reference = InputFile::generateReference();
            $this->currentState->save(false);
        }

        return $this->currentState->input_files_reference;
    }

    ////input image
    /**
     * @inheritdoc
     */
    public function getInputImagesHandler()
    {
        if (!$this->imageInputHandler)
            $this->imageInputHandler = new ImagesInputHandler($this);

        return $this->imageInputHandler;
    }

    /**
     * @inheritdoc
     */
    public function getInputImageBlock($name)
    {
        return $this->getInputImagesHandler()->getInputImageBlock($name);
    }

    /**
     * @inheritdoc
     */
    public function isInputImageBlock($name)
    {
        return $this->getInputImagesHandler()->isInputImageBlock($name);
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
     * @throws FeedbackException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getInputImageReference()
    {
        if (!$this->currentState)
            throw new FeedbackException('This method actual only for concrete feedback state');

        if (!$this->currentState->input_images_reference) {
            $this->currentState->input_images_reference = InputImage::generateReference();
            $this->currentState->save(false);
        }

        return $this->currentState->input_images_reference;
    }

    /////input condition
    /**
     * @inheritdoc
     */
    public function getInputConditionsHandler()
    {
        if (!$this->conditionInputHandler)
            $this->conditionInputHandler = new ConditionsInputHandler($this);

        return $this->conditionInputHandler;
    }

    /**
     * @inheritdoc
     */
    public function getInputCondition($name)
    {
        return $this->getInputConditionsHandler()->getInputCondition($name);
    }

    /**
     * @inheritdoc
     */
    public function isInputCondition($name)
    {
        return $this->getInputConditionsHandler()->isInputCondition($name);
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
     * @return string
     * @throws FeedbackException
     */
    public function getInputConditionReference()
    {
        if (!$this->currentState)
            throw new FeedbackException('This method actual only for concrete feedback state');

        if (!$this->currentState->input_conditions_reference) {
            $this->currentState->input_conditions_reference = InputCondition::generateReference();
            $this->currentState->save(false);
        }

        return $this->currentState->input_conditions_reference;
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

    /**
     * @inheritdoc
     */
    public function offAnnotation()
    {
        $this->needToAnnotate = false;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public function annotate()
    {
        FieldTemplate::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            FieldTemplate::getAnnotationsStringArray($this->getFieldTemplateReference())
        );

        FilesBlock::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            FilesBlock::getAnnotationsStringArray($this->getFileTemplateReference())
        );

        ImagesBlock::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            ImagesBlock::getAnnotationsStringArray($this->getImageTemplateReference())
        );

        ConditionTemplate::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            ConditionTemplate::getAnnotationsStringArray($this->getConditionTemplateReference())
        );

        InputFieldTemplate::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            InputFieldTemplate::getAnnotationsStringArray($this->getInputFieldTemplateReference())
        );

        InputFilesBlock::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            InputFilesBlock::getAnnotationsStringArray($this->getInputFileTemplateReference())
        );

        InputImagesBlock::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            InputImagesBlock::getAnnotationsStringArray($this->getInputImageTemplateReference())
        );

        InputConditionTemplate::setParentFileAnnotator($this);

        $this->getAnnotator()->addAnnotationArray(
            InputConditionTemplate::getAnnotationsStringArray($this->getInputConditionTemplateReference())
        );

        //throw new \yii\base\Exception('There');

        $this->getAnnotator()->finish();
    }

    /**
     * @inheritdoc
     */
    public function onAnnotation()
    {
        $this->needToAnnotate = true;
    }

    /**
     * @inheritdoc
     */
    public function isAnnotationActive()
    {
        return $this->needToAnnotate;
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function getAnnotator()
    {
        if (!is_null($this->annotator)) return $this->annotator;

        $this->annotator = new Annotator();
        $this->annotator->setAnnotatorFileObject($this);
        $this->annotator->prepare();

        return $this->annotator;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFileName()
    {
        return ucfirst(mb_strtolower($this->program_name));
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFilePath()
    {
        $path = Yii::getAlias(CommonModule::getInstance()->yicmsLocation);
        $path .= '/' . FeedbackModule::getInstance()->getModuleName();
        $path .= '/' . CommonModule::getInstance()->annotationsDirectory;

        return $path;
    }

    /**
     * @inheritdoc
     */
    public function getExtendsUseClass()
    {
        return 'Iliich246\YicmsFeedback\Base\Feedback';
    }

    /**
     * @inheritdoc
     */
    public function getExtendsClassName()
    {
        return 'Feedback';
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function getAnnotationTemplateFile()
    {
        $class = new \ReflectionClass(self::class);
        return dirname($class->getFileName())  . '/annotations/feedback.php';
    }

    /**
     * @inheritdoc
     */
    public static function getAnnotationFileNamespace()
    {
        return CommonModule::getInstance()->yicmsNamespace . '\\' .
               FeedbackModule::getInstance()->getModuleName() . '\\' .
               CommonModule::getInstance()->annotationsDirectory;
    }
}
