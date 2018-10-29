<?php

namespace Iliich246\YicmsFeedback\Base;

/**
 * Interface FieldInputReferenceInterface
 *
 * This interface must implement any class, that must has input fields functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface FieldInputReferenceInterface
{
    /**
     * Returns input templateFieldReference
     * @return integer
     */
    public function getInputFieldTemplateReference();

    /**
     * Returns input fieldReference
     * @return integer
     */
    public function getInputFieldReference();
}
