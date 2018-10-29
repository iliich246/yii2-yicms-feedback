<?php

namespace Iliich246\YicmsFeedback\Base;

/**
 * Interface ImageInputReferenceInterface
 *
 * This interface must implement any class, that must has input images functionality.
 *
 * @package Iliich246\YicmsFeedback\Base
 */
interface ImageInputReferenceInterface
{
    /**
     * Returns input imageTemplateReference
     * @return string
     */
    public function getInputImageTemplateReference();

    /**
     * Returns input imageReference
     * @return string
     */
    public function getInputImageReference();
}
