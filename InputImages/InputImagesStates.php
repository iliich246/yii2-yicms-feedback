<?php

namespace Iliich246\YicmsFeedback\InputImages;

use yii\db\ActiveRecord;

/**
 * Class InputImagesStates
 *
 * @property integer $id
 * @property integer $state_id
 * @property string $input_image_reference
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImagesStates extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_images_states}}';
    }
}
