<?php

namespace Iliich246\YicmsFeedback\InputImages;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class InputImagesNamesTranslatesDb
 *
 * @property integer $id
 * @property integer $feedback_input_images_template_id
 * @property integer $common_language_id
 * @property string $dev_name
 * @property string $dev_description
 * @property string $admin_name
 * @property string $admin_description
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImagesNamesTranslatesDb extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_image_names}}';
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
                ['feedback_input_images_template_id'], 'exist', 'skipOnError' => true,
                'targetClass' => InputImagesBlock::class,
                'targetAttribute' => ['feedback_input_images_template_id' => 'id']
            ],
        ];
    }
}
