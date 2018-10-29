<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsCommon\Fields\Field;
use Iliich246\YicmsCommon\Fields\FieldsHandler;

/**
 * Interface FieldsInputInterface
 *
 * This interface must implement any class, that must has input fields functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface FieldsInputInterface
{
    /**
     * Return FieldHandler object, that aggregated in object with input field functionality.
     * @return FieldsHandler
     */
    public function getInputFieldHandler();

    /**
     * This method must proxy FieldHandler method for work with him directly from aggregator.
     * @param string $name
     * @return Field
     */
    public function getInputField($name);
}
