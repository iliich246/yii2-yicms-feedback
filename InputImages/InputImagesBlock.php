<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\db\ActiveQuery;
use yii\helpers\FileHelper;
use yii\validators\RequiredValidator;
use yii\validators\SafeValidator;
use yii\web\UploadedFile;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Annotations\Annotator;
use Iliich246\YicmsCommon\Annotations\AnnotateInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorStringInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\CommonException;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\AbstractEntityBlock;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorBuilderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;
use Iliich246\YicmsFeedback\FeedbackModule;

/**
 * Class InputImagesBlock
 *
 * @property string $input_image_template_reference
 * @property string $validator_reference
 * @property integer $type
 * @property integer $input_image_order
 * @property bool $editable
 * @property bool $active
 * @property integer $max_images
 *
 * @property bool $isActive
 * @property string $key
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImagesBlock extends AbstractEntityBlock implements
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
    const TYPE_ONE_IMAGE    = 0;
    const TYPE_MULTIPLICITY = 1;

    /**
     * @event Event that is triggered before load input images block value
     */
    const EVENT_BEFORE_LOAD = 'beforeLoad';

    /**
     * @event Event that is triggered after load input images block value
     */
    const EVENT_AFTER_LOAD = 'afterLoad';

    /**
     * @event Event that is triggered before save input images block value
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @event Event that is triggered after save input images block value
     */
    const EVENT_AFTER_SAVE = 'afterSave';

    /** @var UploadedFile[]|UploadedFile loaded input image */
    public $inputImage;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var string inputImageReference for what images group must be fetched */
    public $currentInputImageReference;
    /** @inheritdoc */
    protected static $buffer = [];
    /** @var InputImagesNamesTranslatesDb[] buffer for language */
    private $inputImagesNamesTranslations = [];
    /** @var bool keeps fictive state of this input image block */
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
        'input_image_template_reference',
        'validator_reference',
        'type',
        'input_image_order',
        'editable',
        'active',
        'max_files',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_images_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->active    = true;
        $this->editable  = true;
        $this->max_images = 0;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'max_images' => 'Maximum images in block',
            'inputImage' => $this->name(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['editable', 'active'], 'boolean'],
            [['max_images',], 'integer', 'min' => 0],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $prevScenarios = parent::scenarios();
        $scenarios[self::SCENARIO_DEFAULT] = ['inputImage'];
        $scenarios[self::SCENARIO_CREATE] = array_merge($prevScenarios[self::SCENARIO_CREATE],
            [
                'editable',
                'active',
                'max_images',
            ]);
        $scenarios[self::SCENARIO_UPDATE] = array_merge($prevScenarios[self::SCENARIO_UPDATE],
            [
                'editable',
                'active',
                'max_images',
            ]);

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
            self::TYPE_ONE_IMAGE    => 'One image on input block',
            self::TYPE_MULTIPLICITY => 'Multiple images on input block',
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

        $this->trigger(self::EVENT_BEFORE_LOAD);

        if ($this->type == InputImagesBlock::TYPE_ONE_IMAGE) {
            $this->inputImage =
                UploadedFile::getInstance($this, '[' . $this->id . ']inputImage');
        } else {
            $this->inputImage =
                UploadedFile::getInstances($this, '[' . $this->id . ']inputImage');
        }

        if ($this->inputImage) {
            $this->isLoaded = true;
            $this->trigger(self::EVENT_AFTER_LOAD);
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
        return parent::load($data, $formName);
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
        /** @var InputImagesBlock $model */
        foreach ($models as $model)
            if (!$model->isLoaded()) return false;

        return true;
    }

    /**
     * Save input image or group of input files
     * @return bool|void
     */
    public function saveInputImage()
    {
        $this->trigger(self::EVENT_BEFORE_SAVE);

        if (!is_array($this->inputImage)) {
            $success = $this->physicalSaveInputImage($this->inputImage);
            $this->trigger(self::EVENT_AFTER_SAVE);
            return $success;
        } else {
            /** @var UploadedFile $inputImage */
            foreach($this->inputImage as $inputImage)
                if (!$this->physicalSaveInputImage($inputImage)) return false;

            $this->trigger(self::EVENT_AFTER_SAVE);

            return true;
        }
    }

    /**
     * Inner mechanism of input image saving
     * @param UploadedFile $inputImage
     * @return bool
     */
    private function physicalSaveInputImage(UploadedFile $inputImage)
    {
        $path = FeedbackModule::getInstance()->inputImagesPath;

        $name = uniqid() . '.' . $inputImage->extension;
        $inputImage->saveAs($path . $name);

        $inputImageRecord = new InputImage();
        $inputImageRecord->feedback_input_images_template_id = $this->id;
        $inputImageRecord->input_image_reference = $this->currentInputImageReference;
        $inputImageRecord->system_name = $name;
        $inputImageRecord->original_name =  $inputImage->baseName;
        $inputImageRecord->size =  $inputImage->size;
        $inputImageRecord->type = FileHelper::getMimeType($path . $name);

        return $inputImageRecord->save(false);
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($this->scenario === self::SCENARIO_CREATE) {
            $this->input_image_order = $this->maxOrder();
        }

        return parent::save($runValidation, $attributes);
    }

    /**
     * @inheritdoc
     * @return AbstractEntityBlock|InputImagesBlock|null
     * @throws CommonException
     */
    public static function getInstance($templateReference, $programName, $currentInputImageReference = null)
    {
        /** @var InputImagesBlock $value */
        $value = parent::getInstance($templateReference, $programName);

        if (!$value->currentInputImageReference) $value->currentInputImageReference = $currentInputImageReference;

        return $value;
    }

    /**
     * Returns true if input file is active
     * @return bool
     */
    public function isActive()
    {
        if ($this->isNonexistent()) return false;

        return !!$this->active;
    }

    /**
     * Alias of method isActive() for use it via getter like $message->input_name->isActive
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive();
    }

    /**
     * Returns key for working with form
     * @return string
     */
    public function getKey()
    {
        if ($this->type == InputImagesBlock::TYPE_ONE_IMAGE)
            return '[' . $this->id . ']inputImage';

        return '[' . $this->id . ']inputImage[]';
    }

    /**
     * @return bool
     */
    public function isInputImages()
    {
        return $this->isEntities();
    }

    /**
     * @return int
     */
    public function countInputImages()
    {
        return $this->countEntities();
    }

    /**
     * @return \Iliich246\YicmsCommon\Base\AbstractEntity[]|InputImage[]
     */
    public function getInputImages()
    {
        return $this->getEntities();
    }

    /**
     * @return bool|\Iliich246\YicmsCommon\Base\AbstractEntity|InputImage
     */
    public function getInputImage()
    {
        return $this->getEntity();
    }

    /**
     * Returns true if input image block has constraints
     * @return bool
     */
    public function isConstraints()
    {
        if (InputImage::find()->where([
            'feedback_input_images_template_id' => $this->id,
        ])->one()) return true;

        return false;
    }

    /**
     * Returns name of image block for form
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name()
    {
        if ($this->isNonexistent()) return '';

        $inputImageName = $this->getInputImageNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputImageName && trim($inputImageName->admin_name) && CommonModule::isUnderAdmin())
            return $inputImageName->admin_name;

        if ((!$inputImageName || !trim($inputImageName->admin_name)) && CommonModule::isUnderAdmin())
            return $this->program_name;

        if ($inputImageName && trim($inputImageName->admin_name) && CommonModule::isUnderDev())
            return $inputImageName->admin_name . ' (' . $this->program_name . ')';

        if ((!$inputImageName || !trim($inputImageName->admin_name)) && CommonModule::isUnderDev())
            return 'No translate for input image \'' . $this->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns description of input image block
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function description()
    {
        if ($this->isNonexistent()) return '';

        $inputImageName = $this->getInputImageNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputImageName)
            return $inputImageName->admin_description;

        return false;
    }

    /**
     * Returns dev name of input image block for form
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function devName()
    {
        if ($this->isNonexistent()) return '';

        $inputImageName = $this->getInputImageNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputImageName && trim($inputImageName->dev_name) && CommonModule::isUnderAdmin())
            return $inputImageName->dev_name;

        if ((!$inputImageName || !trim($inputImageName->dev_name)) && CommonModule::isUnderAdmin())
            return $this->program_name;

        if ($inputImageName && trim($inputImageName->dev_name) && CommonModule::isUnderDev())
            return $inputImageName->dev_name . ' (' . $this->program_name . ')';

        if ((!$inputImageName || !trim($inputImageName->dev_name)) && CommonModule::isUnderDev())
            return 'No translate for input image \'' . $this->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns dev description of input image block
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function devDescription()
    {
        if ($this->isNonexistent()) return '';

        $inputImageName = $this->getInputImageNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputImageName)
            return $inputImageName->dev_description;

        return false;
    }

    /**
     * Returns buffered name translate db
     * @param LanguagesDb $language
     * @return InputImagesNamesTranslatesDb
     */
    public function getInputImageNameTranslate(LanguagesDb $language)
    {
        if (!array_key_exists($language->id, $this->inputImagesNamesTranslations)) {
            $this->inputImagesNamesTranslations[$language->id] =
                InputImagesNamesTranslatesDb::find()->where([
                    'feedback_input_images_template_id' => $this->id,
                    'common_language_id'                => $language->id,
                ])->one();
        }

        return $this->inputImagesNamesTranslations[$language->id];
    }

    /**
     * @inheritdoc
     */
    public function getEntityQuery()
    {
        if (CommonModule::isUnderDev() || $this->editable) {
            $imageQuery = InputImage::find()
                ->where([
                    'feedback_input_images_template_id' => $this->id,

                ])
                ->indexBy('id');
                //->orderBy(['input_image_order' => SORT_ASC]);

            if ($this->currentInputImageReference)
                $imageQuery->andWhere([
                    'input_image_reference' => $this->currentInputImageReference
                ]);

            return $imageQuery;
        }

        return new ActiveQuery(InputImage::className());
    }

    /**
     * @inheritdoc
     * @throws CommonException
     */
    public function delete()
    {
        if ($this->deleteSequence())
            return parent::delete();

        return false;
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function deleteSequence()
    {
        foreach (InputImagesNamesTranslatesDb::find()->where([
            'feedback_input_images_template_id' => $this->id,
        ])->all() as $inputImageName)
            if (!$inputImageName->delete()) return false;

        $validators = ValidatorDb::find()->where([
            'validator_reference' => $this->validator_reference
        ])->all();

        if ($validators)
            foreach($validators as $validator)
                $validator->delete();

        foreach (InputImage::find()->where([
            'feedback_input_images_template_id' => $this->id,
        ])->all() as $inputImage)
            $inputImage->delete();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getNoExistentEntity()
    {
        $inputImage = new InputImage();
        $inputImage->setNonexistent();

        return $inputImage;
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'input_image_template_reference' => $this->input_image_template_reference,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'input_image_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->input_image_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->input_image_order = $value;
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
        return 'input_image_template_reference';
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
            $safeValidator->attributes = ['inputImage'];
            $this->validators[] = $safeValidator;

            return;
        }

        foreach ($validators as $validator) {

            if ($validator instanceof RequiredValidator && !$this->isNewRecord) continue;

            $validator->attributes = ['inputImage'];
            $this->validators[] = $validator;
        }
    }

    /**
     * @inheritdoc
     * @return ValidatorBuilder
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
     * @throws CommonException
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
        return ucfirst(mb_strtolower($this->program_name)) . 'InputImagesBlock';
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFilePath()
    {
        if (!is_dir(self::$parentFileAnnotator->getAnnotationFilePath() . '/' .
            self::$parentFileAnnotator->getAnnotationFileName()))
            mkdir(self::$parentFileAnnotator->getAnnotationFilePath() . '/' .
                self::$parentFileAnnotator->getAnnotationFileName());

        return self::$parentFileAnnotator->getAnnotationFilePath() . '/' .
        self::$parentFileAnnotator->getAnnotationFileName() . '/InputImages';
    }

    /**
     * @inheritdoc
     */
    public function getExtendsUseClass()
    {
        return 'Iliich246\YicmsFeedback\InputImages\InputImagesBlock;';
    }

    /**
     * @inheritdoc
     */
    public function getExtendsClassName()
    {
        return 'InputImagesBlock';
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function getAnnotationTemplateFile()
    {
        $class = new \ReflectionClass(self::class);
        return dirname($class->getFileName())  . '/annotations/input_images_block.php';
    }

    /**
     * @inheritdoc
     */
    public static function getAnnotationFileNamespace()
    {
        return self::$parentFileAnnotator->getAnnotationFileNamespace() . '\\'
        . self::$parentFileAnnotator->getAnnotationFileName() . '\\'
        . 'InputImages';
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public static function getAnnotationsStringArray($searchData)
    {
        /** @var self[] $templates */
        $templates = self::find()->where([
            'input_image_template_reference' => $searchData
        ])->orderBy([
            'input_image_order' => SORT_ASC
        ])->all();

        if (!$templates) return [];

        $result = [
            ' *' . PHP_EOL,
            ' * INPUT_IMAGES' . PHP_EOL,
        ];

        foreach ($templates as $template) {
            $result[] = ' * @property ' . '\\' .
                $template->getAnnotationFileNamespace() . '\\' .
                $template->getAnnotationFileName() .
                ' $input_' . $template->program_name . ' ' . PHP_EOL;
            $result[] = ' * @property ' . '\\' .
                $template->getAnnotationFileNamespace() . '\\' .
                $template->getAnnotationFileName() .
                ' $input_file_' . $template->program_name . ' ' . PHP_EOL;
            $template->annotate();
        }

        return $result;
    }
}
