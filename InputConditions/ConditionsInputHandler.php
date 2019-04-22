<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class ConditionsInputHandler
 *
 * Object of this class must aggregate any object, that must implement input conditions functionality.
 *
 * @property ConditionsInputReferenceInterface|NonexistentInterface|FictiveInterface|AnnotatorFileInterface $aggregator
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
     * Returns true if aggregator has input condition with name
     * @param $name
     * @return bool
     */
    public function isInputCondition($name)
    {
        if ($this->aggregator->isNonexistent()) return false;

        return InputConditionTemplate::isTemplate($this->aggregator->getInputConditionTemplateReference(), $name);
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

            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
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
                }

                $className = $this->getInputConditionClassName($name);

                if (class_exists($className)) {
                    /** @var InputCondition $fictiveInputCondition */
                    $fictiveInputCondition = new $className();
                    $fictiveInputCondition->setFictive();
                    $fictiveInputCondition->setTemplate($template);
                    $fictiveInputCondition->feedback_value_id = $template->defaultValueId();
                    $fictiveInputCondition->checkbox_state = $template->defaultCheckboxValue();

                    if ($template->type == InputConditionTemplate::TYPE_CHECKBOX)
                        $fictiveInputCondition->value = (string)$fictiveInputCondition->checkbox_state;
                    else
                        $fictiveInputCondition->value = $fictiveInputCondition->feedback_value_id;

                    return $fictiveInputCondition;
                }
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
            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    return InputCondition::getInstance(
                        $this->aggregator->getInputConditionTemplateReference(),
                        $this->aggregator->getInputConditionReference(),
                        $name
                    );
                }

                /** @var InputCondition $className */
                $className = $this->getInputConditionClassName($name);

                if (class_exists($className)) {
                    return  $className::getInstance(
                        $this->aggregator->getInputConditionTemplateReference(),
                        $this->aggregator->getInputConditionReference(),
                        $name
                    );
                }
            }

            return InputCondition::getInstance(
                $this->aggregator->getInputConditionTemplateReference(),
                $this->aggregator->getInputConditionReference(),
                $name
            );
        });
    }

    /**
     * Generates class name of condition
     * @param $name
     * @return string
     */
    private function getInputConditionClassName($name)
    {
        return $this->aggregator->getAnnotationFileNamespace() . '\\' .
               $this->aggregator->getAnnotationFileName() . '\\InputConditions\\' .
               ucfirst(mb_strtolower($name));
    }
}
