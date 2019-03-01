<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\behaviors\TimestampBehavior;
use Iliich246\YicmsCommon\Base\AbstractEntity;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsFeedback\FeedbackModule;

/**
 * Class InputFile
 *
 * @property integer $id
 * @property integer $feedback_input_files_template_id
 * @property string $input_file_reference
 * @property string $system_name
 * @property string $original_name
 * @property integer $input_file_order
 * @property integer $size
 * @property integer $type
 * @property bool $editable
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFile extends AbstractEntity
{
    /** @var bool keeps fictive state of this input file */
    private $isFictive = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_files}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            //'inputFile' => $this->name(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['editable'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [
                //'inputFile'
            ],
            self::SCENARIO_CREATE => [
                //'inputFile'
            ],
            self::SCENARIO_UPDATE => [
                //'inputFile'
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

        if ($this->getInputFileBlock()->type == InputFilesBlock::TYPE_ONE_FILE) {
            $this->inputFile =
                UploadedFile::getInstance($this, '[' . $this->getInputFileBlock()->id . ']inputFile');
        } else {
            $this->inputFile =
                UploadedFile::getInstances($this, '[' . $this->getInputFileBlock()->id . ']inputFile');
        }

        Yii::error(print_r($this->inputFile, true));

        if ($this->inputFile) {
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
        /** @var InputFile $model */
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

        if ($this->scenario == self::SCENARIO_UPDATE) {
            if (file_exists($path . $this->system_name) &&
                !is_dir($path . $this->system_name))
                unlink($path . $this->system_name);
        }

        $name = uniqid() . '.' . $inputFile->extension;
        $inputFile->saveAs($path . $name);

        $inputFileRecord = new self();
        $inputFileRecord->feedback_input_files_template_id = $this->getInputFileBlock()->id;
        $inputFileRecord->input_file_reference = $this->input_file_reference;
        $inputFileRecord->system_name = $name;
        $inputFileRecord->original_name =  $inputFile->baseName;
        $inputFileRecord->size =  $inputFile->size;
        $inputFileRecord->type = FileHelper::getMimeType($path . $name);

        return $inputFileRecord->save(false);
    }

    /**
     * Returns key for working with form
     * @return string
     */
    public function getKey()
    {
        if ($this->getInputFileBlock()->type == InputFilesBlock::TYPE_ONE_FILE)
            return '[' . $this->getInputFileBlock()->id . ']inputFile';

        return '[' . $this->getInputFileBlock()->id . ']inputFile[]';
    }

    /**
     * Return InputFilesBlock associated with this input file entity
     * @return InputFilesBlock
     */
    public function getInputFileBlock()
    {
        return $this->getEntityBlock();
    }

    /**
     * @inheritdoc
     */
    protected static function getReferenceName()
    {
        return 'input_file_reference';
    }

    /**
     * Returns true if input file is active
     * @return bool
     */
    public function isActive()
    {
        if ($this->isNonexistent()) return false;

        return !!$this->getInputFileBlock()->active;
    }

    /**
     * Returns path of file in correct translate
     * @param LanguagesDb|null $language
     * @return bool|string
     */
    public function getPath(LanguagesDb $language = null)
    {
        if ($this->isNonexistent) return false;

        $path = FeedbackModule::getInstance()->filesPatch . $this->system_name;

        if (!file_exists($path) || is_dir($path)) return false;

        return $path;
    }

    /**
     * Returns link for upload this file entity
     * @param LanguagesDb|null $language
     * @param bool|true $onlyPhysicalExistedFiles
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function uploadUrl(LanguagesDb $language= null, $onlyPhysicalExistedFiles = true)
    {
        if ($this->isNonexistent) return false;

        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if ($onlyPhysicalExistedFiles && !$this->getPath()) return false;

        return Url::toRoute([
            '/feedback/input-files/upload-input-file',
            'inputFileBlockId' => $this->getInputFileBlock()->id,
            'inputFileId'      => $this->id,
        ]);
    }


    /**
     * @inheritdoc
     */
    public function entityBlockQuery()
    {
        return InputFilesBlock::find()->where([
            'id' => $this->feedback_input_files_template_id
        ]);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $this->deleteSequence();
        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    protected function deleteSequence()
    {

    }


    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getValidatorReference()
    {
        $fileBlock = $this->getInputFileBlock();

        if (!$fileBlock->validator_reference) {
            $fileBlock->validator_reference = ValidatorBuilder::generateValidatorReference();
            $fileBlock->scenario = InputFilesBlock::SCENARIO_UPDATE;
            $fileBlock->save(false);
        }

        return $fileBlock->validator_reference;
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'feedback_input_files_template_id' => $this->feedback_input_files_template_id,
            'input_file_reference'             => $this->input_file_reference,
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
