<?php

namespace Iliich246\YicmsFeedback\InputImages;

use yii\db\ActiveQuery;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\CommonException;
use Iliich246\YicmsCommon\Base\AbstractEntityBlock;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\FieldReferenceInterface;
use Iliich246\YicmsCommon\Conditions\ConditionTemplate;
use Iliich246\YicmsCommon\Conditions\ConditionsReferenceInterface;
use Iliich246\YicmsCommon\Validators\ValidatorDb;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;

/**
 * Class InputImagesBlock
 *
 * @property string $input_image_template_reference
 * @property string $validator_reference
 * @property integer $input_image_order
 * @property bool $editable
 * @property integer $max_images
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImagesBlock extends AbstractEntityBlock implements ValidatorReferenceInterface
{
    /** @var string inputImageReference for what images group must be fetched */
    private $currentInputImageReference;
    /** @inheritdoc */
    protected static $buffer = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_images_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->editable = true;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'max_images'           => 'Maximum images in block',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['editable'], 'boolean'],
            [['max_images',], 'integer', 'min' => 0],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $prevScenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = array_merge($prevScenarios[self::SCENARIO_CREATE],
            [
                'editable',
                'max_images',
            ]);
        $scenarios[self::SCENARIO_UPDATE] = array_merge($prevScenarios[self::SCENARIO_UPDATE],
            [
                'editable',
                'max_images',
            ]);

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($this->scenario === self::SCENARIO_CREATE) {
            $this->input_image_order = $this->maxOrder();
        }

        return parent::save($runValidation, $attributes);
    }

    /**
     * @inheritdoc
     * @return AbstractEntityBlock|InputImagesBlock|null
     * @throws CommonException
     */
    public static function getInstance($templateReference, $programName, $currentInputImageReference = null)
    {
        /** @var InputImagesBlock $value */
        $value = parent::getInstance($templateReference, $programName);

        if (!$value->currentInputImageReference) $value->currentInputImageReference = $currentInputImageReference;

        return $value;
    }

    /**
     * @return bool
     */
    public function isInputImages()
    {
        return $this->isEntities();
    }

    /**
     * @return int
     */
    public function countInputImages()
    {
        return $this->countEntities();
    }

    /**
     * @return \Iliich246\YicmsCommon\Base\AbstractEntity[]|Image[]
     */
    public function getInputImages()
    {
        return $this->getEntities();
    }

    /**
     * @return bool|\Iliich246\YicmsCommon\Base\AbstractEntity|Image
     */
    public function getInputImage()
    {
        return $this->getEntity();
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
    public function getEntityQuery()
    {
//        if (CommonModule::isUnderDev() || $this->editable)
//            return Image::find()
//                ->where([
//                    'common_images_templates_id' => $this->id,
//                    'image_reference'            => $this->currentImageReference
//                ])
//                ->indexBy('id')
//                ->orderBy(['image_order' => SORT_ASC]);
//
//        return new ActiveQuery(Image::className());
    }

    protected function deleteSequence()
    {

    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'input_image_template_reference' => $this->input_image_template_reference,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'input_image_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->input_image_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->input_image_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function configToChangeOfOrder()
    {
        $this->scenario = self::SCENARIO_CHANGE_ORDER;
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
    protected static function getTemplateReferenceName()
    {
        return 'input_image_template_reference';
    }

    /**
     * @inheritdoc
     * @throws CommonException
     */
    public function getValidatorReference()
    {
        if (!$this->validator_reference) {
            $this->validator_reference = ValidatorBuilder::generateValidatorReference();
            $this->scenario = self::SCENARIO_UPDATE;
            $this->save(false);
        }

        return $this->validator_reference;
    }
}