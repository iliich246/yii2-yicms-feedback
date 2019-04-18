<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class ConditionsInputHandler
 *
 * Object of this class must aggregate any object, that must implement input conditions functionality.
 *
 * @property ConditionsInputReferenceInterface|NonexistentInterface|FictiveInterface $aggregator
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class ConditionsInputHandler extends AbstractHandler
{
    /**
     * FieldsInputHandler constructor.
     * @param ConditionsInputReferenceInterface $aggregator
     */
    public function __construct(ConditionsInputReferenceInterface $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Return instance of input condition by name
     * @param $name
     * @return bool|InputCondition|object
     */
    public function getInputCondition($name)
    {
        if ($this->aggregator->isNonexistent()) {
            $nonexistentInputCondition = new InputCondition();
            $nonexistentInputCondition->setNonexistent();
            $nonexistentInputCondition->setNonexistentName($name);

            return $nonexistentInputCondition;
        }

        if (!$this->aggregator->isFictive()) return $this->forRealCondition($name);

        return $this->forFictiveCondition($name);
    }

    /**
     * Return instance of input condition for fictive condition
     * @param $name
     * @return bool|object
     */
    private function forFictiveCondition($name)
    {
        return $this->getOrSet($name, function() use($name) {

            /** @var InputConditionTemplate $template */
            $template = InputConditionTemplate::getInstance($this->aggregator->getInputConditionTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputCondition = new InputCondition();
                $nonexistentInputCondition->setNonexistent();
                $nonexistentInputCondition->setNonexistentName($name);

                return $nonexistentInputCondition;
            }

            $fictiveInputCondition = new InputCondition();
            $fictiveInputCondition->setFictive();
            $fictiveInputCondition->setTemplate($template);
            $fictiveInputCondition->feedback_value_id = $template->defaultValueId();
            $fictiveInputCondition->checkbox_state = $template->defaultCheckboxValue();

            if ($template->type == InputConditionTemplate::TYPE_CHECKBOX)
                $fictiveInputCondition->value = (string)$fictiveInputCondition->checkbox_state;
            else
                $fictiveInputCondition->value = $fictiveInputCondition->feedback_value_id;

            return $fictiveInputCondition;
        });
    }

    /**
     * Return instance of input condition for real condition
     * @param $name
     * @return bool|object
     */
    private function forRealCondition($name)
    {
        return $this->getOrSet($name, function() use($name) {
            return InputCondition::getInstance(
                $this->aggregator->getInputConditionTemplateReference(),
                $this->aggregator->getInputConditionReference(),
                $name
            );
        });
    }
}
