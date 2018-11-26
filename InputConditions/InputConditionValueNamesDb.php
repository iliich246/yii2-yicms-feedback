<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class InputConditionValueNamesDb
 *
 * @property integer $id
 * @property integer $feedback_input_condition_value_id
 * @property integer $common_language_id
 * @property string $name
 * @property string $description
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionValueNamesDb extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions_value_names}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'string', 'max' => '255'],
            [
                ['common_language_id'], 'exist', 'skipOnError' => true,
                'targetClass' => LanguagesDb::className(), 'targetAttribute' => ['common_language_id' => 'id']
            ],
            [
                ['feedback_input_condition_value_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputConditionValues::className(),
                'targetAttribute' => ['feedback_input_condition_value_id' => 'id']
            ],
        ];
    }
}
