<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Files\FilesBlock;

/**
 * Class FilesInputHandler
 *
 * Object of this class must aggregate any object, that must implement input files functionality.
 *
 * @property FileInputReferenceInterface $aggregator
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FilesInputHandler extends AbstractHandler
{
    /**
     * FilesInputHandler constructor.
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
            return FilesBlock::getInstance(
                $this->aggregator->getInputFileTemplateReference(),
                $name,
                $this->aggregator->getInputFileReference()
            );
        });
    }
}
