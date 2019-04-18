<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Iliich246\YicmsCommon\Annotations\Annotator;
use Iliich246\YicmsCommon\Annotations\AnnotateInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Annotations\AnnotatorStringInterface;
use Iliich246\YicmsCommon\Base\AbstractTemplate;
use Iliich246\YicmsCommon\Validators\ValidatorBuilder;
use Iliich246\YicmsCommon\Validators\ValidatorReferenceInterface;

/**
 * Class InputConditionTemplate
 *
 * @property string $input_condition_template_reference
 * @property string $validator_reference
 * @property integer $input_condition_order
 * @property bool $checkbox_state_default
 * @property integer $type
 * @property bool $editable
 * @property bool $active
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionTemplate extends AbstractTemplate implements
    ValidatorReferenceInterface,
    AnnotateInterface,
    AnnotatorFileInterface,
    AnnotatorStringInterface
{
    const TYPE_CHECKBOX = 0;
    const TYPE_RADIO    = 1;
    const TYPE_SELECT   = 2;

    const DEFAULT_VALUE_TRUE  = 1;
    const DEFAULT_VALUE_FALSE = 0;

    /** @inheritdoc */
    protected static $buffer = [];
    /** @var InputConditionValues[] */
    private $values = null;
    /** @var bool state of annotation necessity */
    private $needToAnnotate = true;
    /** @var Annotator instance */
    private $annotator = null;
    /** @var AnnotatorFileInterface instance */
    private static $parentFileAnnotator;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->editable               = true;
        $this->active                 = true;
        $this->type                   = self::TYPE_CHECKBOX;
        $this->checkbox_state_default = false;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feedback_input_conditions_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['type'], 'integer'],
            [['editable', 'active'], 'boolean'],
            [['checkbox_state_default'], 'boolean']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'checkbox_state_default' => 'Default checkbox value',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $prevScenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = array_merge($prevScenarios[self::SCENARIO_CREATE],
            ['type', 'editable', 'active', 'checkbox_state_default']);
        $scenarios[self::SCENARIO_UPDATE] = array_merge($prevScenarios[self::SCENARIO_UPDATE],
            ['type', 'editable', 'active', 'checkbox_state_default']);

        return $scenarios;
    }

    /**
     * Returns array of input condition types
     * @return array|bool
     */
    public static function getTypes()
    {
        static $array = false;

        if ($array) return $array;

        $array = [
            self::TYPE_CHECKBOX => 'Check box type',
            self::TYPE_RADIO    => 'Radio group type',
            self::TYPE_SELECT   => 'Select dropdown type',
        ];

        return $array;
    }

    /**
     * Returns array of input condition checkbox default values
     * @return array|bool
     */
    public static function getCheckBoxDefaultList()
    {
        static $array = false;

        if ($array) return $array;

        $array = [
            self::DEFAULT_VALUE_FALSE => 'FALSE',
            self::DEFAULT_VALUE_TRUE  => 'TRUE',
        ];

        return $array;
    }

    /**
     * Return name of condition type
     * @return string
     */
    public function getTypeName()
    {
        if (!isset(self::getTypes()[$this->type])) return 'Undefined';

        return self::getTypes()[$this->type];
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($this->scenario === self::SCENARIO_CREATE) {
            $this->input_condition_order = $this->maxOrder();
        }

        return parent::save($runValidation, $attributes);
    }

    /**
     * Returns true if this input condition template has constraints
     * @return bool
     */
    public function isConstraints()
    {
        if (InputCondition::find()->where([
            'input_condition_template_template_id' => $this->id
        ])->one()) return true;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $templateNames = InputConditionsNamesTranslatesDb::find()->where([
            'input_condition_template_template_id' => $this->id,
        ])->all();

        foreach($templateNames as $templateName)
            $templateName->delete();

        $inputConditions = InputCondition::find()->where([
            'input_condition_template_template_id' => $this->id
        ])->all();

        foreach($inputConditions as $inputCondition)
            $inputCondition->delete();

        $inputConditionValues = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $this->id
        ])->all();

        foreach($inputConditionValues as $inputConditionValue)
            $inputConditionValue->delete();

        return parent::delete();
    }

    /**
     * Returns true if input condition template has any values
     * @return bool
     */
    public function isValues()
    {
        if (!is_null($this->values)) return !!count($this->values);

        return !!count($this->getValuesList());
    }

    /**
     * Returns buffered list of values of template
     * @return InputConditionValues[]
     */
    public function getValuesList()
    {
        if (!is_null($this->values)) return $this->values;

        $this->values = InputConditionValues::find()->where([
            'input_condition_template_template_id' => $this->id,
        ])->orderBy(['input_condition_value_order' =>SORT_ASC])
          ->indexBy('id')
          ->all();

        return $this->values;
    }

    /**
     * Returns id of default value
     * @return int|null
     */
    public function defaultValueId()
    {
        foreach($this->getValuesList() as $value) {
            if ($value->is_default) return $value->id;
        }

        return null;
    }

    /**
     * Returns default checkbox value for this template
     * @return bool
     */
    public function defaultCheckboxValue()
    {
        return !!$this->checkbox_state_default;
    }

    /**
     * @inheritdoc
     */
    public static function generateTemplateReference()
    {
        return parent::generateTemplateReference();
    }

    /**
     * @inheritdoc
     */
    public function getOrderQuery()
    {
        return self::find()->where([
            'input_condition_template_reference' => $this->input_condition_template_reference,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getOrderFieldName()
    {
        return 'input_condition_order';
    }

    /**
     * @inheritdoc
     */
    public function getOrderValue()
    {
        return $this->input_condition_order;
    }

    /**
     * @inheritdoc
     */
    public function setOrderValue($value)
    {
        $this->input_condition_order = $value;
    }

    /**
     * @inheritdoc
     */
    public function configToChangeOfOrder()
    {
        //$this->scenario = self::SCENARIO_CHANGE_ORDER;
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
        return 'input_condition_template_reference';
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \yii\base\Exception
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

    /**
     * Sets parent file annotator
     * @param AnnotatorFileInterface $fileAnnotator
     */
    public static function setParentFileAnnotator(AnnotatorFileInterface $fileAnnotator)
    {
        self::$parentFileAnnotator = $fileAnnotator;
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public function annotate()
    {
        $annotationArray = InputConditionTemplateAnnotatorString::getAnnotationsStringArray($this);

        $this->getAnnotator()->addAnnotationArray($annotationArray);

        $this->getAnnotator()->finish(false);
    }

    /**
     * @inheritdoc
     */
    public function offAnnotation()
    {
        $this->needToAnnotate = false;
    }

    /**
     * @inheritdoc
     */
    public function onAnnotation()
    {
        $this->needToAnnotate = true;
    }

    /**
     * @inheritdoc
     */
    public function isAnnotationActive()
    {
        return $this->needToAnnotate;
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function getAnnotator()
    {
        if (!is_null($this->annotator)) return $this->annotator;

        $this->annotator = new Annotator();
        $this->annotator->setAnnotatorFileObject($this);
        $this->annotator->prepare();

        return $this->annotator;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFilePath()
    {
        if (!is_dir(self::$parentFileAnnotator->getAnnotationFilePath() . '/' .
            self::$parentFileAnnotator->getAnnotationFileName()))
            mkdir(self::$parentFileAnnotator->getAnnotationFilePath() . '/' .
                self::$parentFileAnnotator->getAnnotationFileName());

        return self::$parentFileAnnotator->getAnnotationFilePath() . '/' .
        self::$parentFileAnnotator->getAnnotationFileName() . '/InputConditions';
    }

    /**
     * @inheritdoc
     */
    public function getExtendsUseClass()
    {
        return 'Iliich246\YicmsFeedback\InputConditions\InputCondition';
    }

    /**
     * @inheritdoc
     */
    public function getExtendsClassName()
    {
        return 'InputCondition';
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function getAnnotationTemplateFile()
    {
        $class = new \ReflectionClass(self::class);
        return dirname($class->getFileName())  . '/annotations/input_condition.php';
    }

    /**
     * @inheritdoc
     */
    public static function getAnnotationFileNamespace()
    {
        return self::$parentFileAnnotator->getAnnotationFileNamespace() . '\\'
        . self::$parentFileAnnotator->getAnnotationFileName() . '\\'
        . 'InputConditions';
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationFileName()
    {
        return ucfirst(mb_strtolower($this->program_name));
    }

    /**
     * @inheritdoc
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     * @throws \ReflectionException
     */
    public static function getAnnotationsStringArray($searchData)
    {
        /** @var self[] $templates */
        $templates = self::find()->where([
            'input_condition_template_reference' => $searchData
        ])->orderBy([
            'input_condition_order' => SORT_ASC
        ])->all();

        if (!$templates) return [];

        $result = [
            ' *' . PHP_EOL,
            ' * INPUT_CONDITIONS' . PHP_EOL,
        ];

        foreach ($templates as $template) {
            $result[] = ' * @property ' . '\\' .
                $template->getAnnotationFileNamespace() . '\\' .
                $template->getAnnotationFileName() .
                ' $input_' . $template->program_name . ' ' . PHP_EOL;
            $result[] = ' * @property ' . '\\' .
                $template->getAnnotationFileNamespace() . '\\' .
                $template->getAnnotationFileName() .
                ' $input_condition_' . $template->program_name . ' ' . PHP_EOL;
            $template->annotate();
        }

        return $result;
    }
}
