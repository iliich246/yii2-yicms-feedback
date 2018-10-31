<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\db\ActiveRecord;

/**
 * Class InputConditionsStates
 *
 * @property integer $id
 * @property integer $state_id
 * @property string $input_condition_reference
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionsStates extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions_states}}';
    }
}
