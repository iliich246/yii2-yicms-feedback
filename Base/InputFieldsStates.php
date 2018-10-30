<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;

/**
 * Class InputFieldsStates
 *
 * @property integer $id
 * @property integer $state_id
 * @property string $input_field_reference
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFieldsStates extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_fields_states}}';
    }
}
