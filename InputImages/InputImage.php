<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\web\UploadedFile;
use yii\behaviors\TimestampBehavior;
use yii\validators\SafeValidator;
use yii\validators\RequiredValidator;
use Iliich246\YicmsCommon\Base\AbstractEntity;
use Iliich246\YicmsCommon\Base\SortOrderTrait;
use Iliich246\YicmsCommon\Base\SortOrderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorBuilderInterface;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;
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
class InputImage extends AbstractEntity implements
    SortOrderInterface,
    ValidatorBuilderInterface,
    ValidatorReferenceInterface
{
    use SortOrderTrait;

    /** @var UploadedFile loaded input image */
    public $inputImage;
    /** @var ValidatorBuilder instance */
    private $validatorBuilder;

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
            'editable' => 'Editable(dev)'
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
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getPath()
    {
        if ($this->isNonexistent) return false;

        $systemName = $this->system_name;

        $path = FeedbackModule::getInstance()->imagesOriginalsPath . $systemName;

        if (!file_exists($path) || is_dir($path)) return false;

        return $path;
    }

    public function getSrc()
    {

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
        //return parent::delete();
    }

    /**
     * @inheritdoc
     */
    protected function deleteSequence()
    {

    }

    /**
     * Method config validators for this model
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function prepareValidators()
    {
        $validators = $this->getValidatorBuilder()->build();

        if (!$validators) {

            $safeValidator = new SafeValidator();
            $safeValidator->attributes = ['image'];
            $this->validators[] = $safeValidator;

            return;
        }

        foreach ($validators as $validator) {

            if ($validator instanceof RequiredValidator && !$this->isNewRecord) continue;

            $validator->attributes = ['image'];
            $this->validators[] = $validator;
        }
    }

    /**
     * @inheritdoc
     * @return ValidatorBuilder
     */
    public function getValidatorBuilder()
    {
        if ($this->validatorBuilder) return $this->validatorBuilder;

        $this->validatorBuilder = new ValidatorBuilder();
        $this->validatorBuilder->setReferenceAble($this);

        return $this->validatorBuilder;
    }
}
