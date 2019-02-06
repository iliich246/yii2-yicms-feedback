<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\behaviors\TimestampBehavior;
use yii\validators\SafeValidator;
use yii\validators\RequiredValidator;
use Iliich246\YicmsCommon\Base\AbstractEntity;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorBuilderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;
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
 * @property bool $editable
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFile extends AbstractEntity implements
    SortOrderInterface,
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    FictiveInterface,
    NonexistentInterface
{
    use SortOrderTrait;

    /** @var UploadedFile loaded input file */
    public $inputFile;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var bool if true file will behaviour as nonexistent   */
    private $isNonexistent = false;
    /** @var string value for keep program name in nonexistent mode */
    private $nonexistentProgramName;
    /** @var bool keeps fictive state of this input file */
    private $isFictive = false;
    /** @var bool keep state of load */
    private $isLoaded = false;
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
            'editable' => 'Editable(dev)'
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
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
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
        return $this->nonexistentProgramName;
    }

    /**
     * @inheritdoc
     */
    public function setNonexistentName($name)
    {
        $this->nonexistentProgramName = $name;
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
