<?php

namespace Iliich246\YicmsFeedback\Base;

use Iliich246\YicmsCommon\Conditions\ConditionsHandler;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;

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
     * @return ConditionsHandler object, that aggregated in object with conditions functionality.
     */
    public function getInputConditionsHandler();

    /**
     * This method must proxy ConditionTemplate method for work with him directly from aggregator.
     * @param $name
     * @return ConditionTemplate
     */
    public function getInputCondition($name);
}
