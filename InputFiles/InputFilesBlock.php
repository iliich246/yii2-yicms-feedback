<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use yii\db\ActiveQuery;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\AbstractEntityBlock;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;

/**
 * Class InputFilesBlock
 *
 * @property string $input_file_template_reference
 * @property string $validator_reference
 * @property integer $input_file_order
 * @property bool $visible
 * @property bool $editable
 * @property bool $max_files *
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFilesBlock extends AbstractEntityBlock implements ValidatorReferenceInterface
{
    /** @var string inputFileReference for what files group must be fetched */
    private $currentInputFileReference;
    /** @inheritdoc */
    protected static $buffer = [];

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
        $this->visible  = true;
        $this->editable = true;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'max_files' => 'Maximum files in block'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['visible', 'editable'], 'boolean'],
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
        $scenarios[self::SCENARIO_CREATE] = array_merge($prevScenarios[self::SCENARIO_CREATE],
            ['visible', 'editable', 'max_files']);
        $scenarios[self::SCENARIO_UPDATE] = array_merge($prevScenarios[self::SCENARIO_UPDATE],
            ['visible', 'editable', 'max_files']);

        return $scenarios;
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
     * @return bool
     */
    public function isConstraints()
    {
        return true;
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
    protected function deleteSequence()
    {

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
}
