<?php

namespace Iliich246\YicmsFeedback\InputFields;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class InputFieldsNamesTranslatesDb
 *
 * @property integer $id
 * @property integer $feedback_input_fields_template_id
 * @property integer $common_language_id
 * @property string $dev_name
 * @property string $dev_description
 * @property string $admin_name
 * @property string $admin_description
 *
 * @package Iliich246\YicmsFeedback\InputFields
 */
class InputFieldsNamesTranslatesDb extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_field_templates_names}}';
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
                ['feedback_input_fields_template_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputFieldTemplate::class,
                'targetAttribute' => ['feedback_input_fields_template_id' => 'id']
            ],
        ];
    }
}
