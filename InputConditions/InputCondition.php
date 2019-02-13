<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Yii;
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
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputCondition
 *
 * @property integer $id
 * @property integer $input_condition_template_template_id
 * @property string $input_condition_reference
 * @property integer $feedback_value_id
 * @property integer $editable
 * @property integer $checkbox_state
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputCondition extends ActiveRecord implements
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    FictiveInterface,
    NonexistentInterface
{
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
    public static function tableName()
    {
        return '{{%feedback_input_conditions}}';
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
     * Sets InputConditionTemplate for this input condition
     * @param InputConditionTemplate $inputTemplate
     */
    public function setTemplate(InputConditionTemplate $inputTemplate)
    {
        $this->inputTemplate                        = $inputTemplate;
        $this->input_condition_template_template_id = $inputTemplate->id;
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
                'condition_reference' => $value
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
    public function getInputFieldNameTranslate(LanguagesDb $language)
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
