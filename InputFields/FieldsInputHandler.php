<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class FieldsInputHandler
 *
 * Object of this class must aggregate any object, that must implement input fields functionality.
 *
 * @property FieldInputReferenceInterface|NonexistentInterface|FictiveInterface|AnnotatorFileInterface $aggregator
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
     * Returns true if aggregator has input field with name
     * @param $name
     * @return bool
     */
    public function isInputField($name)
    {
        if ($this->aggregator->isNonexistent()) return false;

        return InputFieldTemplate::isTemplate($this->aggregator->getInputFieldTemplateReference(), $name);
    }

    /**
     * Return instance of field for fictive fields
     * @param $name
     * @return bool|object
     */
    private function forFictiveField($name)
    {
        return $this->getOrSet($name, function() use($name) {
            /** @var InputFieldTemplate $template */
            $template =  InputFieldTemplate::getInstance($this->aggregator->getInputFieldTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputField = new InputField();
                $nonexistentInputField->setNonexistent();
                $nonexistentInputField->setNonexistentName($name);

                return $nonexistentInputField;
            }

            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    $fictiveField = new InputField();
                    $fictiveField->setFictive();

                    $fictiveField->setTemplate($template);

                    return $fictiveField;
                }

                $className = $this->getInputFieldClassName($name);

                if (class_exists($className)) {
                    /** @var InputField $fictiveField */
                    $fictiveField = new $className();
                    $fictiveField->setFictive();

                    $fictiveField->setTemplate($template);

                    return $fictiveField;
                }
            }

            $fictiveField = new InputField();
            $fictiveField->setFictive();

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
            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    return InputField::getInstance(
                        $this->aggregator->getInputFieldTemplateReference(),
                        $this->aggregator->getInputFieldReference(),
                        $name
                    );
                }

                /** @var InputField $className */
                $className = $this->getInputFieldClassName($name);

                if (class_exists($className))
                    return $className::getInstance(
                        $this->aggregator->getInputFieldTemplateReference(),
                        $this->aggregator->getInputFieldReference(),
                        $name
                    );
            }
            return InputField::getInstance(
                $this->aggregator->getInputFieldTemplateReference(),
                $this->aggregator->getInputFieldReference(),
                $name
            );
        });
    }

    /**
     * Generates class name of input field
     * @param $name
     * @return string
     */
    private function getInputFieldClassName($name)
    {
        return $this->aggregator->getAnnotationFileNamespace() . '\\' .
               $this->aggregator->getAnnotationFileName() . '\\InputFields\\' .
               ucfirst(mb_strtolower($name));
    }
}
