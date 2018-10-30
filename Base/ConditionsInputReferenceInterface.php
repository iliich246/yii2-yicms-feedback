<?php

namespace Iliich246\YicmsFeedback\Base;

/**
 * Class ConditionsInputReferenceInterface
 *
 * This interface must implement any class, that must has input conditions functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface ConditionsInputReferenceInterface
{
    /**
     * Returns input conditionTemplateReference
     * @return string
     */
    public function getInputConditionTemplateReference();

    /**
     * Returns input conditionReference
     * @return string
     */
    public function getInputConditionReference();
}
