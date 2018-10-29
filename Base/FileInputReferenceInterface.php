<?php

namespace Iliich246\YicmsFeedback\Base;

/**
 * Interface FileInputReferenceInterface
 *
 * This interface must implement any class, that must has files input functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface FileInputReferenceInterface
{
    /**
     * Returns input fileTemplateReference
     * @return string
     */
    public function getInputFileTemplateReference();

    /**
     * Returns in[ut fileReference
     * @return string
     */
    public function getInputFileReference();
}
