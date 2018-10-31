<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Fields\Field;

/**
 * Class FieldsInputHandler
 *
 * Object of this class must aggregate any object, that must implement input fields functionality.
 *
 * @property FieldInputReferenceInterface $aggregator
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
        return $this->getOrSet($name, function() use($name) {
            return Field::getInstance(
                $this->aggregator->getInputFieldTemplateReference(),
                $this->aggregator->getInputFieldReference(),
                $name
            );
        });
    }
}
