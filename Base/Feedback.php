<?php

namespace Iliich246\YicmsFeedback\Base;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;

/**
 * Class Feedback
 *
 * @property integer $id
 * @property string $program_name
 * @property integer $feedback_order
 * @property integer $type
 * @property bool $editable
 * @property bool $visible
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class Feedback extends ActiveRecord implements SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var self[] buffer array */
    private static $feedbackBuffer = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback}}';
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
            'program_name'   => 'Program name',
            'editable'       => 'Editable',
            'visible'        => 'Visible',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => [
                'program_name', 'editable', 'visible'
            ],
            self::SCENARIO_UPDATE => [
                'program_name', 'editable', 'visible'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['program_name', 'required', 'message' => 'Obligatory input field'],
            ['program_name', 'string', 'max' => '50', 'tooLong' => 'Program name must be less than 50 symbols'],
            ['program_name', 'validateProgramName'],
            [['visible', 'editable'], 'boolean']
        ];
    }

    /**
     * Validates the program name.
     * This method serves as the inline validation for page program name.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateProgramName($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $pagesQuery = self::find()->where(['program_name' => $this->program_name]);

            if ($this->scenario == self::SCENARIO_UPDATE)
                $pagesQuery->andWhere(['not in', 'program_name', $this->getOldAttribute('program_name')]);

            $pages = $pagesQuery->all();
            if ($pages)$this->addError($attribute, 'Page with same name already exist in system');
        }
    }

    /**
     * Return instance of page by her name
     * @param $programName
     * @return self
     * @throws FeedbackException
     */
    public static function getByName($programName)
    {
        foreach(self::$feedbackBuffer as $feedback)
            if ($feedback->program_name == $programName)
                return $feedback;

        /** @var self $feedback */
        $feedback = self::find()
            ->where(['program_name' => $programName])
            ->one();

        if ($feedback) {
            self::$feedbackBuffer[$feedback->id] = $feedback;
            return $feedback;
        }

        Yii::error("Сan not find feedback with name " . $programName, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException('Сan not find feedback with name ' . $programName);
        }

        return new self();//TODO: makes mark as empty feedback
    }

    /**
     * Returns instance of feedback by her id
     * @param $id
     * @return Feedback|null
     * @throws FeedbackException
     */
    public static function getInstance($id)
    {
        if (isset(self::$feedbackBuffer[$id]))
            return self::$feedbackBuffer[$id];

        $feedback = self::findOne($id);

        if ($feedback) {
            self::$feedbackBuffer[$feedback->id] = $feedback;
            return $feedback;
        }

        Yii::error("Сan not find feedback with id " . $id, __METHOD__);

        if (defined('YICMS_STRICT')) {
            throw new FeedbackException("Сan not find feedback with id " . $id);
        }

        return new self();//TODO: makes mark as empty essence
    }

    /**
     * Creates new feedback with all service records
     * @return bool
     * @throws FeedbackException
     */
    public function create()
    {
        if ($this->scenario == self::SCENARIO_CREATE) {
            $this->feedback_order = $this->maxOrder();
        }

        if (!$this->save(false))
            throw new FeedbackException('Can not create feedback'. $this->program_name);

        return true;
    }

    /**
     * @return bool
     */
    public function isConstraints()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        return true;
        //return parent::delete();
    }



    /**
     * @inheritdoc
     */
    public function getOrderAble()
    {
        return $this;
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
    public function setOrderValue($value)
    {
        $this->feedback_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->feedback_order;
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'feedback_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find();
    }
}
