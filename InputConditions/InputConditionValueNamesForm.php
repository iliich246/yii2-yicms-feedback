<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class InputConditionValueNamesForm
 *
 * @property InputConditionValueNamesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionValueNamesForm extends AbstractTranslateForm
{
    /** @var string name of value in current model language */
    public $valueName;
    /** @var string description of value on current model language */
    public $valueDescription;
    /** @var InputConditionValues associated with this model */
    private $inputConditionValue;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'valueName'        => 'Input condition value name on language "' . $this->language->name . '"',
            'valueDescription' => 'Input condition value description on language "' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['valueName', 'valueDescription', ], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'valueName', 'valueDescription'
            ],
            self::SCENARIO_UPDATE => [
                'valueName', 'valueDescription'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getViewName()
    {
        return '@yicms-feedback/Views/translates/input_condition_value_name_translate';
    }

    /**
     * Sets InputConditionValues associated with this object
     * @param InputConditionValues $inputConditionValues
     */
    public function setInputConditionValues(InputConditionValues $inputConditionValues)
    {
        $this->inputConditionValue = $inputConditionValues;
    }

    /**
     * Save form data in db
     * @return bool
     */
    public function save()
    {
        $currentTranslate = $this->getCurrentTranslateDb();

        $currentTranslate->name        = $this->valueName;
        $currentTranslate->description = $this->valueDescription;

        return $currentTranslate->save(false);
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->inputConditionValue) return false;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTranslateDb()
    {
        if ($this->currentTranslateDb) return $this->currentTranslateDb;

        $this->currentTranslateDb = InputConditionValueNamesDb::find()
            ->where([
                'common_language_id'                => $this->language->id,
                'feedback_input_condition_value_id' => $this->inputConditionValue->id,
            ])
            ->one();

        if (!$this->currentTranslateDb)
            $this->createTranslateDb();
        else {
            $this->valueName        = $this->currentTranslateDb->name;
            $this->valueDescription = $this->currentTranslateDb->description;
        }

        return $this->currentTranslateDb;
    }

    /**
     * @inheritdoc
     */
    protected function createTranslateDb()
    {
        $this->currentTranslateDb                                    = new InputConditionValueNamesDb();
        $this->currentTranslateDb->common_language_id                = $this->language->id;
        $this->currentTranslateDb->feedback_input_condition_value_id = $this->inputConditionValue->id;

        return $this->currentTranslateDb->save();
    }
}
