<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Yii;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputFieldGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFieldGroup extends AbstractGroup
{
    /** @var FieldInputReferenceInterface|FieldInputInterface inputFieldTemplateReference value for current group */
    protected $fieldInputReference;
    /** @var InputField[] for working with forms */
    public $inputFields;

    /**
     * Sets fieldInputReference object for this
     * @param FieldInputReferenceInterface $fieldInputReference
     */
    public function setFieldInputReference(FieldInputReferenceInterface $fieldInputReference)
    {
        $this->fieldInputReference = $fieldInputReference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function initialize()
    {
        /** @var InputFieldTemplate[] $inputFieldTemplates */
        $inputFieldTemplates = InputFieldTemplate::find()->where([
            'input_field_template_reference' => $this->fieldInputReference->getInputFieldTemplateReference(),
            'active'                         => true,
        ])->all();

        foreach($inputFieldTemplates as $inputFieldTemplate) {
            /** @var InputField $inputField */
            $inputField = $this
                ->fieldInputReference
                ->getInputFieldHandler()
                ->getInputField($inputFieldTemplate->program_name);

            $inputField->prepareValidators();
            $this->inputFields["$inputFieldTemplate->id"] = $inputField;
        }

        return $this->inputFields;
    }

    /**
     * Returns true if this group has active input fields
     * @return bool
     */
    public function isActiveInputFields()
    {
        return !!count($this->inputFields);
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function validate()
    {
        if (!$this->inputFields) return true;

        if (!InputField::isLoadedMultiple($this->inputFields)) {
            $result = '';

            foreach($this->inputFields as $inputField)
                if (!$inputField->isLoaded())
                    $result .= '"' . $inputField->getTemplate()->program_name . '", ';

            $result = substr($result , 0, -2);

            Yii::error(
                'In feedback form don`t used next active input fields: ' .
                $result,  __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException('In feedback form don`t used next active input fields: ' .
                    $result);
            }

            return false;
        }

        $success = true;
        foreach ($this->inputFields as $inputField)
            if (!$inputField->validate()) $success = false;

        return $success;
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return InputField::loadMultipleAnnotated($this->inputFields, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if (!$this->inputFields) return false;

        $success = true;

        foreach($this->inputFields as $inputField) {
            if (!$success) return false;
            $inputField->input_field_reference = $this->fieldInputReference->getInputFieldReference();
            $success = $inputField->save();
        }

        return $success;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function render(ActiveForm $form)
    {
        throw new FeedbackException('Not implemented for developer input fields group (not necessary)');
    }

    /**
     * This method clear all input fields in group
     * @return void
     */
    public function clear()
    {
        if (!$this->inputFields) return;

        foreach($this->inputFields as $inputField) {
            $inputField->value = null;
        }
    }
}
