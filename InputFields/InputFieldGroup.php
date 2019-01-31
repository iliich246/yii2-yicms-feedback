<?php

namespace Iliich246\YicmsFeedback\InputFields;

use yii\base\Model;
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
     */
    public function initialize($inputFieldTemplateId = null)
    {
        /** @var InputFieldTemplate[] $inputFieldTemplates */
        $inputFieldTemplates = InputFieldTemplate::find()->where([
            'input_field_template_reference' => $this->fieldInputReference->getInputFieldTemplateReference(),
            'active'                         => true,
        ])->all();

        foreach($inputFieldTemplates as $inputFieldTemplate) {
            /** @var InputField $inputField */
            $inputField = $this->fieldInputReference->getInputFieldHandler()->getInputField($inputFieldTemplate->program_name);

            $this->inputFields["$inputFieldTemplate->id"] = $inputField;
        }

        return $this->inputFields;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return Model::validateMultiple($this->inputFields);
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return Model::loadMultiple($this->inputFields, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {

    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function render(ActiveForm $form)
    {
        throw new FeedbackException('Not implemented for developer input fields group (not necessary)');
    }
}
