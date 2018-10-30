<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;

/**
 * Class InputFilesStates
 *
 * @property integer $id
 * @property integer $state_id
 * @property string $input_file_reference
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFilesStates extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_files_states}}';
    }
}
