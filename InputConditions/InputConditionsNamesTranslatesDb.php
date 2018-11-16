<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class InputConditionsNamesTranslatesDb
 *
 * @property integer $id
 * @property integer $input_condition_template_template_id
 * @property integer $common_language_id
 * @property string $dev_name
 * @property string $dev_description
 * @property string $admin_name
 * @property string $admin_description
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionsNamesTranslatesDb extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions_names}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dev_name', 'dev_description', 'admin_name', 'admin_description'], 'string'],
            [
                ['common_language_id'], 'exist', 'skipOnError' => true,
                'targetClass' => LanguagesDb::class, 'targetAttribute' => ['common_language_id' => 'id']
            ],
            [
                ['input_condition_template_template_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputConditionTemplate::class,
                'targetAttribute' => ['input_condition_template_template_id' => 'id']
            ],
        ];
    }
}
