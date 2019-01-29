<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\NonexistentInterface;
use Iliich246\YicmsCommon\Fields\Field;
use Iliich246\YicmsCommon\Fields\FieldTemplate;

/**
 * Class FieldsInputHandler
 *
 * Object of this class must aggregate any object, that must implement input fields functionality.
 *
 * @property FieldInputReferenceInterface|NonexistentInterface $aggregator
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FieldsInputHandler extends AbstractHandler
{
    const FICTIVE_PREFIX = '__fictive__';

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
        return $this->forRealField($name);

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
        return $this->getOrSet(self::FICTIVE_PREFIX . $name, function() use($name) {
            $fictiveField = new InputField();
            //$fictiveField->setFictive();

            /** @var FieldTemplate $template */
            $template =  FieldTemplate::getInstance($this->aggregator->getInputFieldTemplateReference(), $name);
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
        if ($this->aggregator->isNonexistent()) {
            $nonexistentInputField = new Field();
            $nonexistentInputField->setNonexistent();
            $nonexistentInputField->setNonexistentName($name);

            return $nonexistentInputField;
        }

        return $this->getOrSet($name, function() use($name) {
            return InputField::getInstance(
                $this->aggregator->getInputFieldTemplateReference(),
                $this->aggregator->getInputFieldReference(),
                $name
            );
        });
    }
}
