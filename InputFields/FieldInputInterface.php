<?php

namespace Iliich246\YicmsFeedback\InputFields;

/**
 * Interface FieldInputInterface
 *
 * This interface must implement any class, that must has input fields functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface FieldInputInterface
{
    /**
     * Return FieldsInputHandler object, that aggregated in object with input field functionality.
     * @return FieldsInputHandler
     */
    public function getInputFieldHandler();

    /**
     * This method must proxy FieldHandler method for work with him directly from aggregator.
     * @param string $name
     * @return InputField
     */
    public function getInputField($name);

    /**
     * Returns true if aggregator has input field with name
     * @param $name
     * @return bool
     */
    public function isInputField($name);
}
