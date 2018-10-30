<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;

/**
 * Class FeedbackState
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackState extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_states}}';
    }
//
}
