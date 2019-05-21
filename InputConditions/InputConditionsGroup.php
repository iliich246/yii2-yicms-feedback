<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Yii;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputConditionsGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionsGroup extends AbstractGroup
{
    /** @var ConditionsInputReferenceInterface|ConditionsInputInterface inputConditionTemplateReference value for current group */
    protected $conditionInputReference;
    /** @var InputCondition[] for working with forms */
    public $inputConditions;

    /**
     * Sets conditionInputReference object for this
     * @param ConditionsInputReferenceInterface $conditionInputReference
     */
    public function setConditionInputReference(ConditionsInputReferenceInterface $conditionInputReference)
    {
        $this->conditionInputReference = $conditionInputReference;
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        /** @var InputConditionTemplate[] $inputConditionsTemplates */
        $inputConditionsTemplates = InputConditionTemplate::find()->where([
            'input_condition_template_reference' => $this->conditionInputReference->getInputConditionTemplateReference(),
            'active'                             => true,
        ])->all();

        foreach($inputConditionsTemplates as $inputConditionsTemplate) {
            /** @var InputCondition $inputConditions */
            $inputConditions = $this
                ->conditionInputReference
                ->getInputConditionsHandler()
                ->getInputCondition($inputConditionsTemplate->program_name);

            $inputConditions->scenario = InputCondition::SCENARIO_INPUT;
            $inputConditions->prepareValidators();
            $this->inputConditions["$inputConditionsTemplate->id"] = $inputConditions;
        }

        return $this->inputConditions;
    }

    /**
     * Returns true if this group has active input images
     * @return bool
     */
    public function isActiveInputConditions()
    {
        return !!count($this->inputConditions);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (!$this->inputConditions) return true;

        if (!InputCondition::isLoadedMultiple($this->inputConditions)) {
            $result = '';

            foreach($this->inputConditions as $inputField)
                if (!$inputField->isLoaded())
                    $result .= '"' . $inputField->getTemplate()->program_name . '", ';

            $result = substr($result , 0, -2);

            Yii::error(
                'In feedback form don`t used next active input conditions: ' .
                $result,  __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException('In feedback form don`t used next active input conditions: ' .
                    $result);
            }

            return false;
        }

        $success = true;
        foreach ($this->inputConditions as $inputCondition)
            if (!$inputCondition->validate()) $success = false;

        return $success;
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return InputCondition::loadMultipleAnnotated($this->inputConditions, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if (!$this->inputConditions) return true;

        $success = true;

        foreach($this->inputConditions as $inputCondition) {
            if (!$success) return false;
            $inputCondition->input_condition_reference = $this->conditionInputReference->getInputConditionReference();
            $success = $inputCondition->save();
        }

        return true;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function render(ActiveForm $form)
    {
        throw new FeedbackException('Not implemented for developer input condition group (not necessary)');
    }

    /**
     * This method clear all input conditions in group
     * @return void
     */
    public function clear()
    {
        if (!$this->inputConditions) return;

        foreach($this->inputConditions as $inputCondition) {
            $inputCondition->value = null;
        }
    }
}
