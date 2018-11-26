<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class InputConditionValues
 *
 * @property integer $id
 * @property integer $input_condition_template_template_id
 * @property string $value_name
 * @property integer $input_condition_value_order
 * @property integer $is_default
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionValues extends ActiveRecord implements SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE       = 0x01;
    const SCENARIO_UPDATE       = 0x02;
    const SCENARIO_CHANGE_ORDER = 0x03;

    /** @var InputConditionTemplate instance associated with this object */
    private $inputConditionTemplate;
    /** @var InputConditionValueNamesDb[] */
    private $translation;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions_values}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'value_name' => 'Value name (will be converted in upper case as constant)'
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'value_name', 'is_default',
            ],
            self::SCENARIO_UPDATE => [
                'value_name', 'is_default',
            ]
        ];
    }

    /**
     * InputConditionTemplate setter
     * @param InputConditionTemplate $inputConditionTemplate
     */
    public function setInputConditionTemplate(InputConditionTemplate $inputConditionTemplate)
    {
        $this->inputConditionTemplate = $inputConditionTemplate;
    }

    /**
     * Fetch InputConditionTemplate from db
     * @return InputConditionTemplate
     */
    public function getConditionTemplate()
    {
        return InputConditionTemplate::getInstanceById($this->input_condition_template_template_id);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['value_name', 'string', 'max' => '255'],
            ['is_default', 'boolean'],
            [
                ['input_condition_template_template_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputConditionTemplate::className(),
                'targetAttribute' => ['input_condition_template_template_id' => 'id']
            ],
            ['value_name', 'validateConditionValueName'],
        ];
    }

    /**
     * Validates the condition value name.
     * This method checks, that for group of condition value name is unique.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateConditionValueName($attribute, $params)
    {
        if (!$this->hasErrors()) {

            if ($this->scenario == self::SCENARIO_CREATE)
                $inputConditionTemplate = $this->inputConditionTemplate->id;
            else
                $inputConditionTemplate = $this->input_condition_template_template_id;

            $query = self::find()->where([
                'input_condition_template_template_id' => $inputConditionTemplate,
                'value_name'                           => $this->value_name,
            ]);

            if ($this->scenario == self::SCENARIO_UPDATE)
                $query->andWhere(['not in', 'value_name', $this->getOldAttribute('value_name')]);

            $count = $query->all();

            if ($count)$this->addError($attribute, 'Value with same name already existed');
        }
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->input_condition_template_template_id = $this->inputConditionTemplate->id;
            $this->input_condition_value_order          = $this->maxOrder();

            if (!self::find()->where([
                'input_condition_template_template_id' => $this->input_condition_template_template_id
            ])->count()) $this->is_default = true;
        }

        if ($this->is_default && $this->scenario != self::SCENARIO_CHANGE_ORDER) {

            /** @var self $other */
            foreach(self::find()->where([
                'common_condition_template_id' => $this->input_condition_template_template_id
            ])->all() as $other) {
                if (!$other->is_default) continue;

                $other->scenario   = self::SCENARIO_UPDATE;
                $other->is_default = false;

                $other->save(false);
            }
        }

        $this->value_name = strtoupper($this->value_name);

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * Returns true if this condition value has constraints
     * @return bool
     */
    public function isConstraints()
    {
        //TODO: implement this method

        return false;
    }

    /**
     * Returns translated name of condition value
     * @param LanguagesDb|null $language
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getName(LanguagesDb $language = null)
    {
        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if (!is_null($this->translation[$language->id])) {
            if (trim($this->translation[$language->id]->name) === '') {
                return $this->value_name;
            }

            return $this->translation[$language->id]->name;
        }

        $this->translation[$language->id] = InputConditionValueNamesDb::find()->where([
            'feedback_input_condition_value_id' => $this->id,
            'common_language_id'                => $language->id,
        ])->one();

        if ($this->translation[$language->id]) {
            if (trim($this->translation[$language->id]->name) === '') {
                return $this->value_name;
            }

            return $this->translation[$language->id]->name;
        }

        return $this->value_name;
    }

    /**
     * Returns translated description of condition value
     * @param LanguagesDb|null $language
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getDescription(LanguagesDb $language = null)
    {
        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        if (!is_null($this->translation[$language->id]))
            return $this->translation[$language->id]->description;

        $this->translation[$language->id] = InputConditionValueNamesDb::find()->where([
            'feedback_input_condition_value_id' => $this->id,
            'common_language_id'                => $language->id,
        ])->one();

        if ($this->translation[$language->id])
            return $this->translation[$language->id]->description;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $inputValuesNames = InputConditionValueNamesDb::find()->where([
            'feedback_input_condition_value_id' => $this->id,
        ])->all();

        foreach($inputValuesNames as $inputValuesName)
            $inputValuesName->delete();

        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'input_condition_template_template_id' => $this->input_condition_template_template_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'input_condition_value_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->input_condition_value_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->input_condition_value_order = $value;
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
}
