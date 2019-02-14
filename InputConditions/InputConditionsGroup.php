<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\base\Model;
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

            $inputConditions->prepareValidators();
            $this->inputConditions["$inputConditions->id"] = $inputConditions;
        }

        return $this->inputConditions;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return Model::validateMultiple($this->inputConditions);
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return Model::loadMultiple($this->inputConditions, $data);
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
        throw new FeedbackException('Not implemented for developer input condition group (not necessary)');
    }
}
