<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Class FeedbackState
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property string $input_fields_reference
 * @property string $input_files_reference
 * @property string $input_images_reference
 * @property string $input_conditions_reference
 * @property integer $is_handled
 * @property integer $created_at
 * @property integer $updated_at
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

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}
