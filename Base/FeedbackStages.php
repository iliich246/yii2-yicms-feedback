<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;

/**
 * Class FeedbackStages
 *
 * @property integer $id
 * @property integer $feedback_id
 * @property string $program_name
 * @property integer $stage_order
 * @property integer $editable
 * @property integer $visible
 * @property string $stage_field_template_reference
 * @property string $stage_file_template_reference
 * @property string $stage_image_template_reference
 * @property string $stage_condition_template_reference
 * @property string $stage_field_reference
 * @property string $stage_file_reference
 * @property string $stage_image_reference
 * @property string $stage_condition_reference
 * @property string $input_field_template_reference
 * @property string $input_file_template_reference
 * @property string $input_image_template_reference
 * @property string $input_condition_template_reference
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackStages extends ActiveRecord implements SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var Feedback instance */
    private $feedback = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_stages}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->visible  = true;
        $this->editable = true;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'program_name' => 'Program Name',
            'editable'     => 'Editable',
            'visible'      => 'Visible',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'program_name', 'required', 'message' => 'Obligatory input field'
            ],
            [
                'program_name', 'string', 'max' => '50', 'tooLong' => 'Program name must be less than 50 symbols'
            ],
            [
                'program_name', 'validateProgramName'
            ],
            [
                ['stage_order'],
                'integer'
            ],
            [
                [
                    'editable',
                    'visible',
                ],
                'boolean'
            ],
            [
                [
                    'stage_field_template_reference',
                    'stage_file_template_reference',
                    'stage_image_template_reference',
                    'stage_condition_template_reference',
                    'stage_field_reference',
                    'stage_file_reference',
                    'stage_image_reference',
                    'stage_condition_reference',
                    'input_field_template_reference',
                    'input_file_template_reference',
                    'input_image_template_reference',
                    'input_condition_template_reference',
                ],
                'string'
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name', 'editable', 'visible',
            ],
            self::SCENARIO_UPDATE => [
                'program_name',  'editable', 'visible',
            ],
        ];
    }

    /**
     * Validates the program name.
     * @param $attribute
     * @param $params
     * @throws FeedbackException
     */
    public function validateProgramName($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $stagesQuery = self::find()->where([
                'feedback_id'  => $this->getFeedback()->id,
                'program_name' => $this->program_name
            ]);

            if ($this->scenario == self::SCENARIO_UPDATE)
                $stagesQuery->andWhere(['not in', 'program_name', $this->getOldAttribute('program_name')]);

            $stages = $stagesQuery->count();
            if ($stages)$this->addError($attribute, 'Feedback with same name already exist in system');
        }
    }

    /**
     * Creates new feedback stage with all service records
     * @return bool
     * @throws FeedbackException
     */
    public function create()
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->stage_order = $this->maxOrder();
            $this->feedback_id = $this->getFeedback()->id;
        }

        if (!$this->save(false))
            throw new FeedbackException('Can not create feedback stage '. $this->program_name);

        return true;
    }

    public function isConstraints()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        return true;
    }

    /**
     * Return associated feedback db instance
     * @return Feedback|null
     * @throws FeedbackException
     */
    public function getFeedback()
    {
        if (!is_null($this->feedback))
            return $this->feedback;

        return $this->feedback = Feedback::getInstance($this->feedback_id);
    }

    /**
     * Feedback setter
     * @param Feedback $feedback
     */
    public function setFeedback(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'feedback_id'  => $this->getFeedback()->id
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'stage_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->stage_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->stage_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function configToChangeOfOrder()
    {
        $this->scenario = self::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
    }
}
