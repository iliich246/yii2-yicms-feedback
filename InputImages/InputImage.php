<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;

use yii\helpers\FileHelper;
use yii\behaviors\TimestampBehavior;
use yii\validators\SafeValidator;
use yii\validators\RequiredValidator;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\AbstractEntity;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsFeedback\FeedbackModule;

/**
 * Class InputImage
 *
 * @property integer $id
 * @property integer $feedback_input_images_template_id
 * @property string $input_image_reference
 * @property string $system_name
 * @property string $original_name
 * @property integer $input_image_order
 * @property integer $size
 * @property bool $editable
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImage extends AbstractEntity
{
    /** @var bool keeps fictive state of this input file */
    private $isFictive = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_images}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'inputImage' => $this->name(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['editable'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [
                'inputImage'
            ],
            self::SCENARIO_CREATE => [
                'inputImage'
            ],
            self::SCENARIO_UPDATE => [
                'inputImage'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Returns key for working with form
     * @return string
     */
    public function getKey()
    {
        if ($this->getInputImagesBlock()->type == InputImagesBlock::TYPE_ONE_IMAGE)
            return '[' . $this->getInputImagesBlock()->id . ']inputImage';

        return '[' . $this->getInputImagesBlock()->id . ']inputImage[]';
    }

    /**
     * @inheritdoc
     * @return InputImagesBlock
     */
    public function getInputImagesBlock()
    {
        return $this->getEntityBlock();
    }

    /**
     * @inheritdoc
     */
    protected static function getReferenceName()
    {
        return 'input_image_reference';
    }

    /**
     * Returns true if input file is active
     * @return bool
     */
    public function isActive()
    {
        if ($this->isNonexistent()) return false;

        return !!$this->getInputImagesBlock()->active;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        if ($this->isNonexistent) return false;

        $path = FeedbackModule::getInstance()->inputImagesPath . $this->system_name;

        if (!file_exists($path) || is_dir($path)) return false;

        return $path;
    }

    /**
     * Return src param for img tag
     * @return string
     */
    public function getSrc()
    {
        return FeedbackModule::getInstance()->inputImagesWebPath . $this->system_name;
    }


    /**
     * @inheritdoc
     */
    public function entityBlockQuery()
    {
        return InputImagesBlock::find()->where([
            'id' => $this->feedback_input_images_template_id
        ]);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $this->deleteSequence();
        return parent::delete();
    }

    /**
     * @inheritdoc
     */
    protected function deleteSequence()
    {
        $path = FeedbackModule::getInstance()->inputImagesPath . $this->system_name;

        if (file_exists($path) && !is_dir($path))
            unlink($path);
    }

    /**
     * @inheritdoc
     */
    public function setFictive()
    {
        $this->isFictive = true;
    }

    /**
     * @inheritdoc
     */
    public function clearFictive()
    {
        $this->isFictive = false;
    }

    /**
     * @inheritdoc
     */
    public function isFictive()
    {
        return $this->isFictive;
    }
}
