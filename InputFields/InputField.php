<?php

namespace Iliich246\YicmsFeedback\InputFields;

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
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputField
 *
 * @property integer $id
 * @property integer $feedback_input_fields_template_id
 * @property integer $input_field_reference
 * @property string $value
 *
 * @property bool $isActive
 * @property string $key
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputField extends ActiveRecord implements
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    FictiveInterface,
    NonexistentInterface
{
    /**
     * @event Event that is triggered before load input field value
     */
    const EVENT_BEFORE_LOAD = 'beforeLoad';

    /**
     * @event Event that is triggered after load input field value
     */
    const EVENT_AFTER_LOAD = 'afterLoad';

    /**
     * @event Event that is triggered before save input field value
     */
    const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @event Event that is triggered after save input field value
     */
    const EVENT_AFTER_SAVE = 'afterSave';

    /** @var InputFieldTemplate instance of field template */
    private $inputTemplate;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var InputFieldsNamesTranslatesDb[] buffer for language */
    private $inputFieldNamesTranslations = [];
    /** @var bool if true field will behaviour as nonexistent */
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
        return '{{%feedback_input_fields_represents}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [
                'value'
            ],
        ];
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
    public function load($data, $formName = null)
    {
        if ($this->isNonexistent()) return false;

        $this->trigger(self::EVENT_BEFORE_LOAD);

        if (parent::load($data, $formName)) {
            $this->isLoaded = true;
            $this->trigger(self::EVENT_AFTER_LOAD);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @param bool|true $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->trigger(self::EVENT_BEFORE_SAVE);

        $success = parent::save($runValidation, $attributeNames);

        $this->trigger(self::EVENT_AFTER_SAVE);

        return $success;
    }

    /**
     * Method for using instead standard loadMultiple for annotated input fields
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
        /** @var InputField $model */
        foreach ($models as $model) {
            if (!$model->isLoaded()) return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->isNonexistent()) return false;

        return parent::delete();
    }

    /**
     * Sets InputFieldTemplate for this input field
     * @param InputFieldTemplate $inputTemplate
     */
    public function setTemplate(InputFieldTemplate $inputTemplate)
    {
        $this->inputTemplate = $inputTemplate;
        $this->feedback_input_fields_template_id = $inputTemplate->id;
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

        return (string)$this->value;
    }


    /**
     * Returns name of input field for form
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function name()
    {
        if ($this->isNonexistent()) return '';

        $inputFieldName = $this->getInputFieldNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFieldName && trim($inputFieldName->admin_name) && CommonModule::isUnderAdmin())
            return $inputFieldName->admin_name;

        if ((!$inputFieldName || !trim($inputFieldName->admin_name)) && CommonModule::isUnderAdmin())
            return $this->getTemplate()->program_name;

        if ($inputFieldName && trim($inputFieldName->admin_name) && CommonModule::isUnderDev())
            return $inputFieldName->admin_name . ' (' . $this->getTemplate()->program_name . ')';

        if ((!$inputFieldName || !trim($inputFieldName->admin_name)) && CommonModule::isUnderDev())
            return 'No translate for input field \'' . $this->getTemplate()->program_name . '\'';

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

        $inputFieldName = $this->getInputFieldNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFieldName)
            return $inputFieldName->admin_description;

        return false;
    }

    /**
     * Returns dev name of input field
     * @return string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function devName()
    {
        if ($this->isNonexistent()) return '';

        $inputFieldName = $this->getInputFieldNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFieldName && trim($inputFieldName->dev_name) && CommonModule::isUnderAdmin())
            return $inputFieldName->dev_name;

        if ((!$inputFieldName || !trim($inputFieldName->dev_name)) && CommonModule::isUnderAdmin())
            return $this->getTemplate()->program_name;

        if ($inputFieldName && trim($inputFieldName->dev_name) && CommonModule::isUnderDev())
            return $inputFieldName->dev_name . ' (' . $this->getTemplate()->program_name . ')';

        if ((!$inputFieldName || !trim($inputFieldName->dev_name)) && CommonModule::isUnderDev())
            return 'No translate for input field \'' . $this->getTemplate()->program_name . '\'';

        return 'Can`t reach this place if all correct';
    }

    /**
     * Returns dev description of input field
     * @return bool|string
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function devDescription()
    {
        if ($this->isNonexistent()) return '';

        $inputFieldName = $this->getInputFieldNameTranslate(Language::getInstance()->getCurrentLanguage());

        if ($inputFieldName)
            return $inputFieldName->dev_description;

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
     * Return fetch from db instance of field
     * @param $inputFieldTemplateReference
     * @param $inputFieldReference
     * @param $programName
     * @return InputField|null
     * @throws FeedbackException
     */
    public static function getInstance($inputFieldTemplateReference, $inputFieldReference, $programName)
    {
        if (is_null($inputTemplate = InputFieldTemplate::getInstance($inputFieldTemplateReference, $programName))) {
            Yii::warning(
                "Can`t fetch for " . static::className() .
                " name = $programName and inputFieldTemplateReference = $inputFieldTemplateReference",
                __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException(
                    "YICMS_STRICT_MODE:
                Can`t fetch for " . static::className() .
                    " name = $programName and inputFieldTemplateReference = $inputFieldTemplateReference");
            }

            return null;
        };

        /** @var self $inputField */
        $inputField = self::find()->where([
            'feedback_input_fields_template_id' => $inputTemplate->id,
            'input_field_reference'             => $inputFieldReference
        ])->one();

        if ($inputField) {
            $inputField->inputTemplate = $inputTemplate;
            return $inputField;
        }

        Yii::warning(
            "Can`t fetch for " . static::className() . " name = $programName and inputFieldReference = $inputFieldReference",
            __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException(
                "YICMS_STRICT_MODE:
                Can`t fetch for " . static::className() . " name = $programName and inputFieldReference = $inputFieldReference");
        }

        return null;
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
                'input_field_reference' => $value
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
     * @return InputFieldTemplate
     */
    public function getTemplate()
    {
        if ($this->inputTemplate) return $this->inputTemplate;

        return $this->inputTemplate = InputFieldTemplate::getInstanceById($this->feedback_input_fields_template_id);
    }

    /**
     * Method configs validators for this model
     * @throws \Iliich246\YicmsCommon\Base\CommonException
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
        $fieldTemplate = $this->getTemplate();

        if (!$fieldTemplate->validator_reference) {
            $fieldTemplate->validator_reference = ValidatorBuilder::generateValidatorReference();
            $fieldTemplate->scenario = InputFieldTemplate::SCENARIO_UPDATE;
            $fieldTemplate->save(false);
        }

        return $fieldTemplate->validator_reference;
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
     * @return InputFieldsNamesTranslatesDb
     */
    public function getInputFieldNameTranslate(LanguagesDb $language)
    {
        if (!array_key_exists($language->id, $this->inputFieldNamesTranslations)) {
            $this->inputFieldNamesTranslations[$language->id] =
                InputFieldsNamesTranslatesDb::find()->where([
                    'feedback_input_fields_template_id' => $this->getTemplate()->id,
                    'common_language_id'                => $language->id,
            ])->one();
        }

        return $this->inputFieldNamesTranslations[$language->id];
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
