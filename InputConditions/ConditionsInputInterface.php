<?php

namespace Iliich246\YicmsFeedback\InputConditions;

/**
 * Interface ConditionsInputInterface
 *
 * This interface must implement any class, that must has input conditions functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface ConditionsInputInterface
{
    /**
     * @return ConditionsInputHandler object, that aggregated in object with conditions functionality.
     */
    public function getInputConditionsHandler();

    /**
     * This method must proxy InputConditionTemplate method for work with him directly from aggregator.
     * @param $name
     * @return InputConditionTemplate
     */
    public function getInputCondition($name);
}
