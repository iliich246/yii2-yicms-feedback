<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\base\Component;
use Iliich246\YicmsCommon\Annotations\AnnotatorStringInterface;

/**
 * Class InputConditionTemplateAnnotatorString
 *
 * This class needed only for generation annotation string.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionTemplateAnnotatorString extends Component implements AnnotatorStringInterface
{
    /**
     * @inheritdoc
     * @param InputConditionTemplate $searchData
     *
     */
    public static function getAnnotationsStringArray($searchData)
    {
        $result = [];

        foreach ($searchData->getValuesList() as $value) {
            $result[] =   "   const " . $value->value_name .
                ' = ' . "'" . $value->value_name . "'" .  ";" . PHP_EOL;
        }

        return $result;
    }
}
