<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\validators\SafeValidator;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorBuilderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;
use Iliich246\YicmsFeedback\FeedbackModule;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputCondition
 *
 * @property integer $id
 * @property integer $input_condition_template_template_id
 * @property string $input_condition_reference
 * @property integer $feedback_value_id
 * @property integer $checkbox_state
 *
 * @property bool $isActive
 * @property string $key
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputCondition extends ActiveRecord implements
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    FictiveInterface,
    NonexistentInterface
{
    const SCENARIO_INPUT = 0x01;

    /** @var string value of condition */
    public $value;
    /** @var InputConditionTemplate instance of input condition template */
    private $inputTemplate = null;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var InputConditionsNamesTranslatesDb[] buffer for language */
    private $inputConditionNamesTranslations = [];
    /** @var bool if true condition will behaviour as nonexistent   */
    private $isNonexistent = false;
    /** @var string value for keep program name in nonexistent mode */
    private $nonexistentProgramName;
    /** @var bool keeps fictive state of this input field */
    private $isFictive = false;
    /** @var bool keep state of load */
    private $isLoaded = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, function() {

            if ($this->getTemplate()->type == InputConditionTemplate::TYPE_CHECKBOX) {
                $this->value = !!$this->checkbox_state;
                return;
            };

            if (is_null($this->feedback_value_id)) {
                $valueId = $this->getTemplate()->defaultValueId();

                if (!is_null($valueId)) {

                    $this->feedback_value_id = $valueId;
                    $this->simpleSave();
                }
            }

            $this->value = $this->feedback_value_id;
        });
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'value' => $this->name(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['value', 'safe'],
            ['value', 'validateValue'],
            ['input_condition_reference', 'string', 'max' => '255'],
            [['checkbox_state'], 'boolean'],
            [
                ['feedback_value_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputConditionValues::className(),
                'targetAttribute' => ['feedback_value_id' => 'id']
            ],
            [
                ['input_condition_template_template_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputConditionValues::className(),
                'targetAttribute' => ['input_condition_template_template_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [
                'value',
                'input_condition_reference',
                'checkbox_state',
                'feedback_value_id',
                'input_condition_template_template_id'
            ],
            self::SCENARIO_INPUT => [
                'value'
            ]
        ];
    }

    /**
     * Validates value.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateValue($attribute, $params)
    {
        if (!$this->hasErrors()) {

            if ($this->getTemplate()->type == InputConditionTemplate::TYPE_CHECKBOX) return;

            $conditionValue = InputConditionValues::findOne($this->value);

            if (!$conditionValue)
                $this->addError($attribute, 'Wrong value');
        }
    }

    /**
     * Returns fetch from db instance of condition
     * @param $inputConditionTemplateReference
     * @param $inputConditionReference
     * @param $programName
     * @return null
     * @throws FeedbackException
     */
    public static function getInstance($inputConditionTemplateReference, $inputConditionReference, $programName)
    {
        if (is_null($template = InputConditionTemplate::getInstance($inputConditionTemplateReference, $programName))) {
            Yii::warning(
                "Can`t fetch for " . static::className() .
                " name = $programName and inputConditionTemplateReference = $inputConditionTemplateReference",
                __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException(
                    "YICMS_STRICT_MODE:
                Can`t fetch for " . static::className() .
                    " name = $programName and inputConditionTemplateReference = $inputConditionTemplateReference");
            }

            return null;
        };

        /** @var self $inputCondition */
        $inputCondition = self::find()->where([
            'input_condition_template_template_id' => $template->id,
            'input_condition_reference'            => $inputConditionReference,
        ])->one();

        if ($inputCondition) {
            $inputCondition->template = $template;
            return $inputCondition;
        }

        Yii::warning(
            "Can`t fetch for " . static::className() . " name = $programName and inputConditionReference =
            $inputConditionReference",
            __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException(
                "YICMS_STRICT_MODE:
                Can`t fetch for " . static::className() . " name = $programName and inputConditionReference =
                $inputConditionReference");
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        if ($this->isNonexistent()) return false;

        if (parent::load($data, $formName)) {
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
        /** @var InputCondition $model */
        foreach ($models as $model) {
            if (!$model->isLoaded()) return false;
        }

        return true;
    }

    /**
     * Method for using instead standard loadMultiple for annotated input conditions
     * Standard Model::loadMultiple not work because he find $formName only once
     * @param Model[] $models
     * @param $data
     * @return bool
     */
    public static function loadMultipleAnnotated($models, $data)
    {
        $success = false;

        foreach ($models as $i => $model) {
            $formName = $model->formName();

            if ($formName == '') {
                if (!empty($data[$i]) && $model->load($data[$i], '')) {
                    $success = true;
                }
            } elseif (!empty($data[$formName][$i]) && $model->load($data[$formName][$i], '')) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->getTemplate()->type == InputConditionTemplate::TYPE_CHECKBOX) {
            if (!$this->value)
                $this->checkbox_state = false;
            else
                $this->checkbox_state = true;
        }else {
            $this->feedback_value_id = $this->value;
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * Save action that just proxy parent method
     * @return bool
     */
    public function simpleSave()
    {
        return parent::save(false);
    }

    /**
     * Returns true if condition has any values
     * @return bool
     */
    public function isValues()
    {
        if ($this->isNonexistent)
            return false;

        return $this->getTemplate()->isValues();
    }

    /**
     * Returns list of values for drop down lists
     * @param LanguagesDb|null $language
     * @return array
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getValuesTranslatedArray(LanguagesDb $language = null)
    {
        if (!$language) $language = Language::getInstance()->getCurrentLanguage();

        $conditionValues = $this->getTemplate()->getValuesList();

        $array = [];

        foreach($conditionValues as $index => $value)
            $array[$index] = $value->getName($language);

        return $array;
    }


    /**
     * Sets InputConditionTemplate for this input condition
     * @param InputConditionTemplate $inputTemplate
     */
    public function setTemplate(InputConditionTemplate $inputTemplate)
    {
        $this->inputTemplate                        = $inputTemplate;
        $this->input_condition_template_template_id = $inputTemplate->id;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function __toString()
    {
        if ($this->isNonexistent() && CommonModule::isUnderDev() && defined('YICMS_ALERTS'))
            return '(Dev)Nonexistent condition';

        if ($this->isNonexistent()) return '';

        if ($this->getTemplate()->type == InputConditionTemplate::TYPE_CHECKBOX) {
            if ($this->checkbox_state) return FeedbackModule::t('app', 'True');
            return FeedbackModule::t('app', 'False');
        } else {
            $conditionValue = InputConditionValues::findOne($this->feedback_value_id);

            if (!$conditionValue) return '';
            return $conditionValue->name();
        }
    }

    /**
     * Returns name of input condition for form
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name()
    {
        if ($this->isNonexistent()) return '';

        $inputConditionName = $this->getInputConditionNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputConditionName && trim($inputConditionName->admin_name) && CommonModule::isUnderAdmin())
            return $inputConditionName->admin_name;

        if ((!$inputConditionName || !trim($inputConditionName->admin_name)) && CommonModule::isUnderAdmin())
            return $this->getTemplate()->program_name;

        if ($inputConditionName && trim($inputConditionName->admin_name) && CommonModule::isUnderDev())
            return $inputConditionName->admin_name . ' (' . $this->getTemplate()->program_name . ')';

        if ((!$inputConditionName || !trim($inputConditionName->admin_name)) && CommonModule::isUnderDev())
            return 'No translate for input condition \'' . $this->getTemplate()->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns description of field
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function description()
    {
        if ($this->isNonexistent()) return '';

        $inputConditionName = $this->getInputConditionNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputConditionName)
            return $inputConditionName->admin_description;

        return false;
    }

    /**
     * Returns dev name of input condition
     * @return string
     */
    public function devName()
    {
        if ($this->isNonexistent()) return '';

        $inputConditionName = $this->getInputConditionNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputConditionName && trim($inputConditionName->dev_name) && CommonModule::isUnderAdmin())
            return $inputConditionName->dev_name;

        if ((!$inputConditionName || !trim($inputConditionName->dev_name)) && CommonModule::isUnderAdmin())
            return $this->getTemplate()->program_name;

        if ($inputConditionName && trim($inputConditionName->dev_name) && CommonModule::isUnderDev())
            return $inputConditionName->dev_name . ' (' . $this->getTemplate()->program_name . ')';

        if ((!$inputConditionName || !trim($inputConditionName->dev_name)) && CommonModule::isUnderDev())
            return 'No translate for input condition \'' . $this->getTemplate()->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns dev description of input condition
     * @return string
     */
    public function devDescription()
    {
        if ($this->isNonexistent()) return '';

        $inputConditionName = $this->getInputConditionNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputConditionName)
            return $inputConditionName->dev_description;

        return false;
    }

    /**
     * Returns true, if field is active
     * @return bool
     */
    public function isActive()
    {
        if ($this->isNonexistent()) return false;

        return !!$this->getTemplate()->active;
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
     * Generates reference key
     * @return string
     * @throws FeedbackException
     */
    public static function generateReference()
    {
        $value = strrev(uniqid());

        $coincidence = true;
        $counter = 0;

        while($coincidence) {
            if (!self::find()->where([
                'input_condition_reference' => $value
            ])->one()) return $value;

            if ($counter++ > 100) {
                Yii::error('Looping', __METHOD__);
                throw new FeedbackException('Looping in ' . __METHOD__);
            }
        }

        throw new FeedbackException('Can`t reach there 0_0' . __METHOD__);
    }

    /**
     * Returns key for working with form
     * @return string
     */
    public function getKey()
    {
        return '[' . $this->getTemplate()->id . ']value';
    }

    /**
     * Return instance of input field template object
     * @return InputConditionTemplate
     */
    public function getTemplate()
    {
        if ($this->inputTemplate) return $this->inputTemplate;

        return $this->inputTemplate = InputConditionTemplate::getInstanceById($this->input_condition_template_template_id);
    }

    /**
     * Method configs validators for this model
     * @return void
     */
    public function prepareValidators()
    {
        $validators = $this->getValidatorBuilder()->build();

        if (!$validators) {

            $safeValidator = new SafeValidator();
            $safeValidator->attributes = ['value'];
            $this->validators[] = $safeValidator;

            return;
        }

        foreach ($validators as $validator)
            $this->validators[] = $validator;
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
        $inputTemplate = $this->getTemplate();

        if (!$inputTemplate->validator_reference) {
            $inputTemplate->validator_reference = ValidatorBuilder::generateValidatorReference();
            $inputTemplate->scenario = InputConditionTemplate::SCENARIO_UPDATE;
            $inputTemplate->save(false);
        }

        return $inputTemplate->validator_reference;
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
     * Returns buffered name translate db
     * @param LanguagesDb $language
     * @return InputConditionsNamesTranslatesDb
     */
    public function getInputConditionNameTranslate(LanguagesDb $language)
    {
        if (!array_key_exists($language->id, $this->inputConditionNamesTranslations)) {
            $this->inputConditionNamesTranslations[$language->id] =
                InputConditionsNamesTranslatesDb::find()->where([
                    'input_condition_template_template_id' => $this->getTemplate()->id,
                    'common_language_id'                   => $language->id,
                ])->one();
        }

        return $this->inputConditionNamesTranslations[$language->id];
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
