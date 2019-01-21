<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class FeedbackNamesTranslatesDb
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $common_language_id
 * @property string $name
 * @property string $description
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackNamesTranslatesDb extends ActiveRecord
{
    /** @var array buffer of translates in view $buffer[<feedback-id>][<language-id>] */
    private static $buffer;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_names_translates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feedback_id', 'common_language_id'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['common_language_id'], 'exist', 'skipOnError' => true, 'targetClass' => LanguagesDb::className(), 'targetAttribute' => ['common_language_id' => 'id']],
            [['feedback_id'], 'exist', 'skipOnError' => true, 'targetClass' => Feedback::className(), 'targetAttribute' => ['feedback_id' => 'id']],
        ];
    }

    /**
     * Return buffered translation
     * @param $feedbackId
     * @param $languageId
     * @return null|self
     */
    public static function getTranslate($feedbackId, $languageId)
    {
        if (!isset(self::$buffer[$feedbackId][$languageId]) &&
            !is_null(self::$buffer[$feedbackId][$languageId])) {
            self::$buffer[$feedbackId][$languageId] = self::find()->where([
                'feedback_id' => $feedbackId,
                'common_language_id' => $languageId,
            ])->one();
        }

        return self::$buffer[$feedbackId][$languageId];
    }
}
