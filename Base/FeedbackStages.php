<?php

namespace Iliich246\YicmsFeedback\Base;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Fields\Field;
use Iliich246\YicmsCommon\Fields\FieldsHandler;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\FieldsInterface;
use Iliich246\YicmsCommon\Fields\FieldReferenceInterface;
use Iliich246\YicmsCommon\Files\File;
use Iliich246\YicmsCommon\Files\FilesBlock;
use Iliich246\YicmsCommon\Files\FilesHandler;
use Iliich246\YicmsCommon\Files\FilesInterface;
use Iliich246\YicmsCommon\Files\FilesReferenceInterface;
use Iliich246\YicmsCommon\Images\Image;
use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Images\ImagesHandler;
use Iliich246\YicmsCommon\Images\ImagesInterface;
use Iliich246\YicmsCommon\Images\ImagesReferenceInterface;
use Iliich246\YicmsCommon\Conditions\Condition;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsCommon\Conditions\ConditionsHandler;
use Iliich246\YicmsCommon\Conditions\ConditionsInterface;
use Iliich246\YicmsCommon\Conditions\ConditionsReferenceInterface;

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
class FeedbackStages extends ActiveRecord implements
    FieldsInterface,
    FieldReferenceInterface,
    FilesInterface,
    FilesReferenceInterface,
    ImagesInterface,
    ImagesReferenceInterface,
    ConditionsReferenceInterface,
    ConditionsInterface,
    SortOrderInterface
{
    use SortOrderTrait;

    const SCENARIO_CREATE = 0;
    const SCENARIO_UPDATE = 1;

    /** @var FieldsHandler instance of field handler object */
    private $fieldHandler;
    /** @var FilesHandler instance of file handler object */
    private $fileHandler;
    /** @var ImagesHandler instance of image handler object */
    private $imageHandler;
    /** @var ConditionsHandler instance of condition handler object */
    private $conditionHandler;
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
     */
    public function getFieldHandler()
    {
        if (!$this->fieldHandler)
            $this->fieldHandler = new FieldsHandler($this);

        return $this->fieldHandler;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getField($name)
    {
        return $this->getFieldHandler()->getField($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldTemplateReference()
    {
        if (!$this->stage_field_template_reference) {
            $this->stage_field_template_reference = FieldTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_field_template_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFieldReference()
    {
        if (!$this->stage_field_reference) {
            $this->stage_field_reference = Field::generateReference();
            $this->save(false);
        }

        return $this->stage_field_reference;
    }

    /**
     * @inheritdoc
     */
    public function getFileHandler()
    {
        if (!$this->fileHandler)
            $this->fileHandler = new FilesHandler($this);

        return $this->fileHandler;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileReference()
    {
        if (!$this->stage_file_reference) {
            $this->stage_file_reference = File::generateReference();
            $this->save(false);
        }

        return $this->stage_file_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getFileTemplateReference()
    {
        if (!$this->stage_file_template_reference) {
            $this->stage_file_template_reference = FilesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_file_template_reference;
    }

    /**
     * @inheritdoc
     */
    public function getFileBlock($name)
    {
        return $this->getFileHandler()->getFileBlock($name);
    }

    /**
     * @inheritdoc
     */
    public function getImagesHandler()
    {
        if (!$this->imageHandler)
            $this->imageHandler = new ImagesHandler($this);

        return $this->imageHandler;
    }

    /**
     * @inheritdoc
     */
    public function getImageBlock($name)
    {
        return $this->getImagesHandler()->getImageBlock($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageTemplateReference()
    {
        if (!$this->stage_image_template_reference) {
            $this->stage_image_template_reference = ImagesBlock::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_image_template_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getImageReference()
    {
        if (!$this->stage_image_reference) {
            $this->stage_image_reference = Image::generateReference();
            $this->save(false);
        }

        return $this->stage_image_reference;
    }

    /**
     * @inheritdoc
     */
    public function getConditionsHandler()
    {
        if (!$this->conditionHandler)
            $this->conditionHandler = new ConditionsHandler($this);

        return $this->conditionHandler;
    }

    /**
     * @inheritdoc
     */
    public function getCondition($name)
    {
        return $this->getConditionsHandler()->getCondition($name);
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionTemplateReference()
    {
        if (!$this->stage_condition_template_reference) {
            $this->stage_condition_template_reference = ConditionTemplate::generateTemplateReference();
            $this->save(false);
        }

        return $this->stage_condition_template_reference;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function getConditionReference()
    {
        if (!$this->stage_condition_reference) {
            $this->stage_condition_reference = Condition::generateReference();
            $this->save(false);
        }

        return $this->stage_condition_reference;
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