<?php

namespace Iliich246\YicmsFeedback\Base;

/**
 * Class FeedbackConfigDb
 *
 * @property int $id
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackConfigDb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_config}}';
    }
}
