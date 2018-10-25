<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class FeedbackStagesNamesTranslatesDb
 *
 * @property int $id
 * @property int $stage_id
 * @property int $common_language_id
 * @property string $name
 * @property string $description
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackStagesNamesTranslatesDb extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_stages_names_translates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stage_id', 'common_language_id'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['common_language_id'], 'exist', 'skipOnError' => true, 'targetClass' => LanguagesDb::className(), 'targetAttribute' => ['common_language_id' => 'id']],
            [['stage_id'], 'exist', 'skipOnError' => true, 'targetClass' => FeedbackStages::className(), 'targetAttribute' => ['stage_id' => 'id']],
        ];
    }
}
