<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Yii;
use yii\db\ActiveRecord;
use yii\validators\SafeValidator;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
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
 * @property integer $editable
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputField extends ActiveRecord implements
    ValidatorBuilderInterface,
    ValidatorReferenceInterface,
    NonexistentInterface
{
    /** @var InputFieldTemplate instance of field template */
    private $inputTemplate;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;
    /** @var bool if true field will behaviour as nonexistent   */
    private $isNonexistent = false;
    /** @var string value for keep program name in nonexistent mode */
    private $nonexistentProgramName;

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
    public function delete()
    {
        parent::delete();
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
                'field_reference' => $value
            ])->one()) return $value;

            if ($counter++ > 100) {
                Yii::error('Looping', __METHOD__);
                throw new FeedbackException('Looping in ' . __METHOD__);
            }
        }

        throw new FeedbackException('Can`t reach there 0_0' . __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function isEditable()
    {
        return (bool)$this->editable;
    }

    /**
     * @inheritdoc
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
     * Method config validators for this model
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
}
