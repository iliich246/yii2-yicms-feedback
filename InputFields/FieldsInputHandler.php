<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class FieldsInputHandler
 *
 * Object of this class must aggregate any object, that must implement input fields functionality.
 *
 * @property FieldInputReferenceInterface|NonexistentInterface|FictiveInterface $aggregator
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FieldsInputHandler extends AbstractHandler
{
    /**
     * FieldsInputHandler constructor.
     * @param FieldInputReferenceInterface $aggregator
     */
    public function __construct(FieldInputReferenceInterface $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Return instance of field by name
     * @param $name
     * @return bool|object
     */
    public function getInputField($name)
    {
        if ($this->aggregator->isNonexistent()) {
            $nonexistentInputField = new InputField();
            $nonexistentInputField->setNonexistent();
            $nonexistentInputField->setNonexistentName($name);

            return $nonexistentInputField;
        }

        if (!$this->aggregator->isFictive()) return $this->forRealField($name);

        return $this->forFictiveField($name);
    }

    /**
     * Return instance of field for fictive fields
     * @param $name
     * @return bool|object
     */
    private function forFictiveField($name)
    {
        return $this->getOrSet($name, function() use($name) {
            $fictiveField = new InputField();
            $fictiveField->setFictive();

            /** @var InputFieldTemplate $template */
            $template =  InputFieldTemplate::getInstance($this->aggregator->getInputFieldTemplateReference(), $name);
            $fictiveField->setTemplate($template);

            return $fictiveField;
        });
    }

    /**
     * Return instance of input field for real fields
     * @param $name
     * @return bool|object
     */
    private function forRealField($name)
    {
        return $this->getOrSet($name, function() use($name) {
            return InputField::getInstance(
                $this->aggregator->getInputFieldTemplateReference(),
                $this->aggregator->getInputFieldReference(),
                $name
            );
        });
    }
}
