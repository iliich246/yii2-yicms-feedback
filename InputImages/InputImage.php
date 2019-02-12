<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use yii\behaviors\TimestampBehavior;
use yii\validators\SafeValidator;
use yii\validators\RequiredValidator;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\AbstractEntity;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorBuilderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;
use Iliich246\YicmsFeedback\FeedbackModule;

/**
 * Class InputImage
 *
 * @property integer $id
 * @property integer $feedback_input_images_template_id
 * @property string $input_image_reference
 * @property string $system_name
 * @property string $original_name
 * @property integer $input_image_order
 * @property integer $size
 * @property bool $editable
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImage extends AbstractEntity implements
    SortOrderInterface,
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    FictiveInterface
{
    use SortOrderTrait;

    /** @var UploadedFile loaded input image */
    public $inputImage;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var InputImagesNamesTranslatesDb[] buffer for language */
    private $inputImagesNamesTranslations = [];
    /** @var bool keeps fictive state of this input file */
    private $isFictive = false;
    /** @var bool keep state of load */
    private $isLoaded = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_images}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'editable' => 'Editable(dev)'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['editable'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [
                'inputImage'
            ],
            self::SCENARIO_CREATE => [
                'inputImage'
            ],
            self::SCENARIO_UPDATE => [
                'inputImage'
            ]
        ];
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
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        if ($this->isNonexistent()) return false;

        if ($this->getInputImagesBlock()->type == InputImagesBlock::TYPE_ONE_IMAGE) {
            $this->inputImage =
                UploadedFile::getInstance($this, '[' . $this->getInputImagesBlock()->id . ']inputImage');
        } else {
            $this->inputImage =
                UploadedFile::getInstances($this, '[' . $this->getInputImagesBlock()->id . ']inputImage');
        }

        if ($this->inputImage) {
            $this->isLoaded = true;
            return true;
        }

        return false;
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
        /** @var InputImage $model */
        foreach ($models as $model)
            if (!$model->isLoaded()) return false;

        return true;
    }

    /**
     * Save input image or group of input files
     * @return bool|void
     */
    public function saveInputFile()
    {
        if (!is_array($this->inputImage)) {
            return $this->physicalSaveInputImage($this->inputFile);
        } else {
            $success = true;

            /** @var UploadedFile $inputFile */
            foreach($this->inputImage as $inputFile)
                if (!$this->physicalSaveInputImage($inputFile)) return false;

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

        if ($this->scenario == self::SCENARIO_UPDATE) {
            if (file_exists($path . $this->system_name) &&
                !is_dir($path . $this->system_name))
                unlink($path . $this->system_name);
        }

        $name = uniqid() . '.' . $inputImage->extension;
        $inputImage->saveAs($path . $name);

        $inputImageRecord = new self();
        $inputImageRecord->feedback_input_images_template_id = $this->getInputImagesBlock()->id;
        $inputImageRecord->input_image_reference = $this->input_image_reference;
        $inputImageRecord->system_name = $name;
        $inputImageRecord->original_name =  $inputImage->baseName;
        $inputImageRecord->size =  $inputImage->size;
        $inputImageRecord->type = FileHelper::getMimeType($path . $name);

        return $inputImageRecord->save(false);
    }

    /**
     * Returns key for working with form
     * @return string
     */
    public function getKey()
    {
        if ($this->getInputImagesBlock()->type == InputImagesBlock::TYPE_ONE_IMAGE)
            return '[' . $this->getInputImagesBlock()->id . ']inputImage';

        return '[' . $this->getInputImagesBlock()->id . ']inputImage[]';
    }

    /**
     * @inheritdoc
     * @return InputImagesBlock
     */
    public function getInputImagesBlock()
    {
        return $this->getEntityBlock();
    }

    /**
     * @inheritdoc
     */
    protected static function getReferenceName()
    {
        return 'input_image_reference';
    }

    /**
     * @inheritdoc
     */

    public function getPath()
    {/*
        if ($this->isNonexistent) return false;

        $systemName = $this->system_name;

        $path = FeedbackModule::getInstance()->imagesOriginalsPath . $systemName;

        if (!file_exists($path) || is_dir($path)) return false;

        return $path;
*/
    }

    public function getSrc()
    {

    }


    /**
     * @inheritdoc
     */
    public function entityBlockQuery()
    {
        return InputImagesBlock::find()->where([
            'id' => $this->feedback_input_images_template_id
        ]);
    }

    /**
     * @inheritdoc
     */

    public function delete()
    {
        $this->deleteSequence();
        //return parent::delete();
    }

    /**
     * @inheritdoc
     */
    protected function deleteSequence()
    {

    }

    /**
     * Returns name of file for form
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
            return $this->getInputImagesBlock()->program_name;

        if ($inputImageName && trim($inputImageName->admin_name) && CommonModule::isUnderDev())
            return $inputImageName->admin_name . ' (' . $this->getInputImagesBlock()->program_name . ')';

        if ((!$inputImageName || !trim($inputImageName->admin_name)) && CommonModule::isUnderDev())
            return 'No translate for input image \'' . $this->getInputImagesBlock()->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns description of input file
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
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getValidatorReference()
    {
        $inputImagesBlock = $this->getInputImagesBlock();

        if (!$inputImagesBlock->validator_reference) {
            $inputImagesBlock->validator_reference = ValidatorBuilder::generateValidatorReference();
            $inputImagesBlock->scenario = InputImagesBlock::SCENARIO_UPDATE;
            $inputImagesBlock->save(false);
        }

        return $inputImagesBlock->validator_reference;
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'feedback_input_images_template_id' => $this->feedback_input_images_template_id,
            'input_image_reference'             => $this->input_image_reference,
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
        //$this->scenario = self::SCENARIO_CHANGE_ORDER;
    }

    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
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
                    'feedback_input_images_template_id' => $this->getInputImagesBlock()->id,
                    'common_language_id'                => $language->id,
                ])->one();
        }

        return $this->inputImagesNamesTranslations[$language->id];
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
