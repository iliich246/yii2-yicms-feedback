<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputCondition
 *
 * @property integer $id
 * @property integer $input_condition_template_template_id
 * @property string $input_condition_reference
 * @property integer $feedback_value_id
 * @property integer $editable
 * @property integer $checkbox_state
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputCondition extends ActiveRecord
{
    /** @var InputConditionTemplate instance of input condition template */
    private $template = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions}}';
    }

    /**
     * Returns fetch from db instance of condition
     * @param $inputConditionTemplateReference
     * @param $inputConditionReference
     * @param $programName
     * @return null
     * @throws FeedbackException
     */
    public static function getInstance($inputConditionTemplateReference, $inputConditionReference, $programName)
    {
        if (is_null($template = InputConditionTemplate::getInstance($inputConditionTemplateReference, $programName))) {
            Yii::warning(
                "Can`t fetch for " . static::className() .
                " name = $programName and inputConditionTemplateReference = $inputConditionTemplateReference",
                __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException(
                    "YICMS_STRICT_MODE:
                Can`t fetch for " . static::className() .
                    " name = $programName and inputConditionTemplateReference = $inputConditionTemplateReference");
            }

            return null;
        };

        /** @var self $inputCondition */
        $inputCondition = self::find()->where([
            'input_condition_template_template_id' => $template->id,
            'input_condition_reference'            => $inputConditionReference,
        ])->one();

        if ($inputCondition) {
            $inputCondition->template = $template;
            return $inputCondition;
        }

        Yii::warning(
            "Can`t fetch for " . static::className() . " name = $programName and inputConditionReference =
            $inputConditionReference",
            __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException(
                "YICMS_STRICT_MODE:
                Can`t fetch for " . static::className() . " name = $programName and inputConditionReference =
                $inputConditionReference");
        }

        return null;
    }

    /**
     * Generates reference key
     * @return string
     * @throws FeedbackException
     */
    public static function generateReference()
    {
        $value = strrev(uniqid());

        $coincidence = true;
        $counter = 0;

        while($coincidence) {
            if (!self::find()->where([
                'condition_reference' => $value
            ])->one()) return $value;

            if ($counter++ > 100) {
                Yii::error('Looping', __METHOD__);
                throw new FeedbackException('Looping in ' . __METHOD__);
            }
        }

        throw new FeedbackException('Can`t reach there 0_0' . __METHOD__);
    }
}
