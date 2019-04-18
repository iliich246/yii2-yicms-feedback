<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Yii;
use yii\db\ActiveQuery;
use yii\validators\RequiredValidator;
use yii\validators\SafeValidator;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Annotations\Annotator;
use Iliich246\YicmsCommon\Annotations\AnnotateInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorStringInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\AbstractEntityBlock;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorBuilderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;
use Iliich246\YicmsFeedback\FeedbackModule;

/**
 * Class InputFilesBlock
 *
 * @property string $input_file_template_reference
 * @property string $validator_reference
 * @property integer $type
 * @property integer $input_file_order
 * @property bool $active
 * @property bool $editable
 * @property bool $max_files
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFilesBlock extends AbstractEntityBlock implements
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    FictiveInterface,
    AnnotateInterface,
    AnnotatorFileInterface,
    AnnotatorStringInterface
{
    /**
     * Input files types
     */
    const TYPE_ONE_FILE     = 0;
    const TYPE_MULTIPLICITY = 1;

    /** @var UploadedFile[]|UploadedFile loaded input file */
    public $inputFile;
    /** @var string inputFileReference for what files group must be fetched */
    public $currentInputFileReference;
    /** @inheritdoc */
    protected static $buffer = [];
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var InputFilesNamesTranslatesDb[] buffer for language */
    private $inputFilesNamesTranslations = [];
    /** @var bool keeps fictive state of this input file */
    private $isFictive = false;
    /** @var bool keep state of load */
    private $isLoaded = false;
    /** @var bool state of annotation necessity */
    private $needToAnnotate = true;
    /** @var Annotator instance */
    private $annotator = null;
    /** @var AnnotatorFileInterface instance */
    private static $parentFileAnnotator;
    /** @var array of exception words for magical getter/setter */
    protected static $annotationExceptionWords = [
        'id',
        'isNewRecord',
        'scenario',
        'program_name',
        'input_file_template_reference',
        'validator_reference',
        'type',
        'input_file_order',
        'editable',
        'visible',
        'active',
        'max_files',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_files_templates}}';
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
        return array_merge(parent::attributeLabels(),[
            'inputFile' => $this->name(),
            'max_files' => 'Maximum files in block'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['active', 'editable'], 'boolean'],
            ['max_files', 'integer', 'min' => 0],
            ['max_files', 'default', 'value' => 0]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $prevScenarios = parent::scenarios();
        $scenarios[self::SCENARIO_DEFAULT] = ['inputFile'];
        $scenarios[self::SCENARIO_CREATE] = array_merge($prevScenarios[self::SCENARIO_CREATE],
            ['active', 'editable', 'max_files', 'inputFile']);
        $scenarios[self::SCENARIO_UPDATE] = array_merge($prevScenarios[self::SCENARIO_UPDATE],
            ['active', 'editable', 'max_files', 'inputFile']);

        return $scenarios;
    }

    /**
     * Return array of input file types
     * @return array|bool
     */
    public static function getTypes()
    {
        static $array = false;

        if ($array) return $array;

        $array = [
            self::TYPE_ONE_FILE     => 'One file on input block',
            self::TYPE_MULTIPLICITY => 'Multiple files on input block',
        ];

        return $array;
    }

    /**
     * Return name of type of concrete field
     * @return mixed
     */
    public function getTypeName()
    {
        return self::getTypes()[$this->type];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        if ($this->isNonexistent()) return false;

        if ($this->type == InputFilesBlock::TYPE_ONE_FILE) {
            $this->inputFile =
                UploadedFile::getInstance($this, '[' . $this->id . ']inputFile');
        } else {
            $this->inputFile =
                UploadedFile::getInstances($this, '[' . $this->id . ']inputFile');
        }

        if ($this->inputFile) {
            $this->isLoaded = true;
            return true;
        }

        return false;
    }

    /**
     * Load method for dev part
     * @param $data
     * @param null $formName
     * @return bool
     */
    public function loadDev($data, $formName = null)
    {
        return parent::load($data, $formName = null);
    }

    /**
     * Returns true if this model is loaded
     * @return bool
     */
    public function isLoaded()
    {
        if ($this->isNonexistent()) return false;

        return $this->isLoaded;
    }

    /**
     * Makes is loaded method for group of models
     * @param $models
     * @return bool
     */
    public static function isLoadedMultiple($models)
    {
        /** @var InputFilesBlock $model */
        foreach ($models as $model) {
            if (!$model->isLoaded()) return false;

        }

        return true;
    }

    /**
     * Save input file or group of input files
     * @return bool
     */
    public function saveInputFile()
    {
        if (!is_array($this->inputFile)) {
            return $this->physicalSaveInputFile($this->inputFile);
        } else {
            /** @var UploadedFile $inputFile */
            foreach($this->inputFile as $inputFile)
                if (!$this->physicalSaveInputFile($inputFile)) return false;

            return true;
        }
    }

    /**
     * Inner mechanism of input file saving
     * @param UploadedFile $inputFile
     * @return bool
     */
    private function physicalSaveInputFile(UploadedFile $inputFile)
    {
        $path = FeedbackModule::getInstance()->inputFilesPatch;

        $name = uniqid() . '.' . $inputFile->extension;
        $inputFile->saveAs($path . $name);

        $inputFileRecord = new InputFile();
        $inputFileRecord->feedback_input_files_template_id = $this->id;
        $inputFileRecord->input_file_reference = $this->currentInputFileReference;
        $inputFileRecord->system_name = $name;
        $inputFileRecord->original_name =  $inputFile->baseName;
        $inputFileRecord->size =  $inputFile->size;
        $inputFileRecord->type = FileHelper::getMimeType($path . $name);

        return $inputFileRecord->save(false);
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($this->scenario === self::SCENARIO_CREATE) {
            $this->input_file_order = $this->maxOrder();
        }

        return parent::save($runValidation, $attributes);
    }

    /**
     * Returns name of input file block for form
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name()
    {
        if ($this->isNonexistent()) return '';

        $inputFileName = $this->getInputFileNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFileName && trim($inputFileName->admin_name) && CommonModule::isUnderAdmin())
            return $inputFileName->admin_name;

        if ((!$inputFileName || !trim($inputFileName->admin_name)) && CommonModule::isUnderAdmin())
            return $this->program_name;

        if ($inputFileName && trim($inputFileName->admin_name) && CommonModule::isUnderDev())
            return $inputFileName->admin_name . ' (' . $this->program_name . ')';

        if ((!$inputFileName || !trim($inputFileName->admin_name)) && CommonModule::isUnderDev())
            return 'No translate for input file block \'' . $this->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns description of input file block
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function description()
    {
        if ($this->isNonexistent()) return '';

        $inputFileName = $this->getInputFileNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFileName)
            return $inputFileName->admin_description;

        return false;
    }

    /**
     * Returns dev name of input file block
     * @return string
     */
    public function devName()
    {
        if ($this->isNonexistent()) return '';

        $inputFileName = $this->getInputFileNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFileName && trim($inputFileName->dev_name) && CommonModule::isUnderAdmin())
            return $inputFileName->dev_name;

        if ((!$inputFileName || !trim($inputFileName->dev_name)) && CommonModule::isUnderAdmin())
            return $this->program_name;

        if ($inputFileName && trim($inputFileName->dev_name) && CommonModule::isUnderDev())
            return $inputFileName->dev_name . ' (' . $this->program_name . ')';

        if ((!$inputFileName || !trim($inputFileName->dev_name)) && CommonModule::isUnderDev())
            return 'No translate for input file \'' . $this->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns dev description of input file block
     * @return string
     */
    public function devDescription()
    {
        if ($this->isNonexistent()) return '';

        $inputFileName = $this->getInputFileNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFileName)
            return $inputFileName->dev_description;

        return false;
    }


    /**
     * @inheritdoc
     */
    public static function getInstance($templateReference, $programName, $currentInputFileReference = null)
    {
        /** @var self $value */
        $value = parent::getInstance($templateReference, $programName);

        if (!$value->currentInputFileReference) $value->currentInputFileReference = $currentInputFileReference;

        return $value;
    }

    /**
     * Return true if input file block has constrains
     * @return bool
     */
    public function isConstraints()
    {
        if (InputFile::find()->where([
            'feedback_input_files_template_id' => $this->id
        ])->one()) return true;

        return false;
    }

    /**
     * Renames parent method on concrete name
     * @return InputFile
     */
    public function getInputFile()
    {
        return $this->getEntity();
    }

    /**
     * Renames parent method on concrete name
     * @return InputFile[]
     */
    public function getInputFiles()
    {
        return $this->getEntities();
    }

    /**
     * Sets current input file reference
     * @param $inputFileReference
     */
    public function setInputFileReference($inputFileReference)
    {
        $this->currentInputFileReference = $inputFileReference;
    }

    /**
     * @inheritdoc
     */
    public function getEntityQuery()
    {
        if (CommonModule::isUnderDev() || $this->editable) {
            $fileQuery = InputFile::find()
                ->where([
                    'feedback_input_files_template_id' => $this->id,
                ])
                ->indexBy('id')
                ->orderBy(['input_file_order' => SORT_ASC]);

            if ($this->currentInputFileReference)
                $fileQuery->andWhere([
                    'input_file_reference' => $this->currentInputFileReference]);

            return $fileQuery;
        }

        return new ActiveQuery(InputFile::className());
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->deleteSequence())
            return parent::delete();

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function deleteSequence()
    {
        foreach(InputFilesNamesTranslatesDb::find()->where([
            'feedback_input_files_template_id' => $this->id,
        ])->all() as $inputFileName)
            if (!$inputFileName->delete()) return false;

        $validators = ValidatorDb::find()->where([
            'validator_reference' => $this->validator_reference
        ])->all();

        if ($validators)
            foreach($validators as $validator)
                $validator->delete();

        foreach(InputFile::find()->where([
            'feedback_input_files_template_id' => $this->id,
        ])->all() as $inputFile)
            $inputFile->delete();

        return true;
    }

    /**
     * Returns buffered name translate db
     * @param LanguagesDb $language
     * @return InputFilesNamesTranslatesDb
     */
    public function getInputFileNameTranslate(LanguagesDb $language)
    {
        if (!array_key_exists($language->id, $this->inputFilesNamesTranslations)) {
            $this->inputFilesNamesTranslations[$language->id] =
                InputFilesNamesTranslatesDb::find()->where([
                    'feedback_input_files_template_id'  => $this->id,
                    'common_language_id'                => $language->id,
                ])->one();
        }

        return $this->inputFilesNamesTranslations[$language->id];
    }

    /**
     * Returns true if input file is active
     * @return bool
     */
    public function isActive()
    {
        if ($this->isNonexistent()) return false;

        return $this->active;
    }

    /**
     * Returns key for working with form
     * @return string
     */
    public function getKey()
    {
        if ($this->type == InputFilesBlock::TYPE_ONE_FILE)
            return '[' . $this->id . ']inputFile';

        return '[' . $this->id . ']inputFile[]';
    }

    /**
     * @inheritdoc
     */
    protected function getNoExistentEntity()
    {
        $inputFile = new InputFile();
        $inputFile->setNonexistent();

        return $inputFile;
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'input_file_template_reference' => $this->input_file_template_reference,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'input_file_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->input_file_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->input_file_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function configToChangeOfOrder()
    {
        $this->scenario = self::SCENARIO_CHANGE_ORDER;
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
    protected static function getTemplateReferenceName()
    {
        return 'input_file_template_reference';
    }

    /**
     * Method config validators for this model
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function prepareValidators()
    {
        $validators = $this->getValidatorBuilder()->build();

        if (!$validators) {

            $safeValidator = new SafeValidator();
            $safeValidator->attributes = ['inputFile'];
            $this->validators[] = $safeValidator;

            return;
        }

        foreach ($validators as $validator) {

            if ($validator instanceof RequiredValidator && !$this->isNewRecord) continue;

            $validator->attributes = ['inputFile'];
            $this->validators[] = $validator;
        }
    }

    /**
     * @inheritdoc
     */
    public function getValidatorBuilder()
    {
        if ($this->validatorBuilder) return $this->validatorBuilder;

        $this->validatorBuilder = new ValidatorBuilder();
        $this->validatorBuilder->setReferenceAble($this);

        return $this->validatorBuilder;
    }


    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getValidatorReference()
    {
        if (!$this->validator_reference) {
            $this->validator_reference = ValidatorBuilder::generateValidatorReference();
            $this->scenario = self::SCENARIO_UPDATE;
            $this->save(false);
        }

        return $this->validator_reference;
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public function annotate()
    {
        $this->getAnnotator()->finish();
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
     * Sets parent file annotator
     * @param AnnotatorFileInterface $fileAnnotator
     */
    public static function setParentFileAnnotator(AnnotatorFileInterface $fileAnnotator)
    {
        self::$parentFileAnnotator = $fileAnnotator;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFileName()
    {
        return ucfirst(mb_strtolower($this->program_name)) . 'InputFileBlock';
    }
}
