<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Iliich246\YicmsCommon\Base\AbstractTemplate;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;

/**
 * Class InputConditionTemplate
 *
 * @property string $input_condition_template_reference
 * @property string $validator_reference
 * @property integer $input_condition_order
 * @property bool $checkbox_state_default
 * @property integer $type
 * @property bool $editable
 * @property bool $active
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionTemplate extends AbstractTemplate implements ValidatorReferenceInterface
{
    const TYPE_CHECKBOX = 0;
    const TYPE_RADIO    = 1;
    const TYPE_SELECT   = 2;

    const DEFAULT_VALUE_TRUE  = 1;
    const DEFAULT_VALUE_FALSE = 0;

    /** @inheritdoc */
    protected static $buffer = [];
    /** @var InputConditionValues[] */
    private $values = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->editable               = true;
        $this->active                 = true;
        $this->type                   = self::TYPE_CHECKBOX;
        $this->checkbox_state_default = false;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['type'], 'integer'],
            [['editable', 'active'], 'boolean'],
            [['checkbox_state_default'], 'boolean']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'checkbox_state_default' => 'Default checkbox value',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $prevScenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = array_merge($prevScenarios[self::SCENARIO_CREATE],
            ['type', 'editable', 'active', 'checkbox_state_default']);
        $scenarios[self::SCENARIO_UPDATE] = array_merge($prevScenarios[self::SCENARIO_UPDATE],
            ['type', 'editable', 'active', 'checkbox_state_default']);

        return $scenarios;
    }

    /**
     * Returns array of input condition types
     * @return array|bool
     */
    public static function getTypes()
    {
        static $array = false;

        if ($array) return $array;

        $array = [
            self::TYPE_CHECKBOX => 'Check box type',
            self::TYPE_RADIO    => 'Radio group type',
            self::TYPE_SELECT   => 'Select dropdown type',
        ];

        return $array;
    }

    /**
     * Returns array of input condition checkbox default values
     * @return array|bool
     */
    public static function getCheckBoxDefaultList()
    {
        static $array = false;

        if ($array) return $array;

        $array = [
            self::DEFAULT_VALUE_FALSE => 'FALSE',
            self::DEFAULT_VALUE_TRUE  => 'TRUE',
        ];

        return $array;
    }

    /**
     * Return name of condition type
     * @return string
     */
    public function getTypeName()
    {
        if (!isset(self::getTypes()[$this->type])) return 'Undefined';

        return self::getTypes()[$this->type];
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($this->scenario === self::SCENARIO_CREATE) {
            $this->input_condition_order = $this->maxOrder();
        }

        return parent::save($runValidation, $attributes);
    }

    /**
     * Returns true if this input condition template has constraints
     * @return bool
     */
    public function isConstraints()
    {
        if (InputCondition::find()->where([
            'input_condition_template_template_id' => $this->id
        ])->one()) return true;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $templateNames = InputConditionsNamesTranslatesDb::find()->where([
            'input_condition_template_template_id' => $this->id,
        ])->all();

        foreach($templateNames as $templateName)
            $templateName->delete();

        $inputConditions = InputCondition::find()->where([
            'input_condition_template_template_id' => $this->id
        ])->all();

        foreach($inputConditions as $inputCondition)
            $inputCondition->delete();

        $inputConditionValues = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $this->id
        ])->all();

        foreach($inputConditionValues as $inputConditionValue)
            $inputConditionValue->delete();

        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    public static function generateTemplateReference()
    {
        return parent::generateTemplateReference();
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'input_condition_template_reference' => $this->input_condition_template_reference,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'input_condition_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->input_condition_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->input_condition_order = $value;
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
    protected static function getTemplateReferenceName()
    {
        return 'input_condition_template_reference';
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \yii\base\Exception
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
