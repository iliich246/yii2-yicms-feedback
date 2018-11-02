<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsCommon\Fields\Field;

/**
 * Class InputField
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputField extends Field
{
    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [
                'value'
            ],
        ];
    }
}
