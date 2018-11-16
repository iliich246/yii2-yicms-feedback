<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class InputConditionNamesTranslatesForm
 *
 * @property InputConditionsNamesTranslatesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionNamesTranslatesForm extends AbstractTranslateForm
{
    /** @var string name of page in current model language */
    public $devName;
    /** @var string description of page on current model language */
    public $devDescription;
    /** @var string name of page in current model language */
    public $adminName;
    /** @var string description of page on current model language */
    public $adminDescription;
    /** @var InputConditionTemplate associated with this model */
    private $inputConditionTemplate;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'devName'          => 'Condition name on language "' . $this->language->name . '"',
            'devDescription'   => 'Description of condition on language "' . $this->language->name . '"',
            'adminName'        => 'Condition name on language "' . $this->language->name . '"',
            'adminDescription' => 'Description of condition on language "' . $this->language->name . '"',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['devName', 'devDescription', 'adminName', 'adminDescription'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getViewName()
    {
        return '@yicms-feedback/Views/translates/input_condition_name_translate';
    }

    /**
     * Sets InputConditionTemplate associated with this object
     * @param InputConditionTemplate $inputConditionTemplate
     */
    public function setInputConditionTemplate(InputConditionTemplate $inputConditionTemplate)
    {
        $this->inputConditionTemplate = $inputConditionTemplate;
    }

    /**
     * Saves record in data base
     * @return bool
     */
    public function save()
    {
        $this->getCurrentTranslateDb()->dev_name                             = $this->devName;
        $this->getCurrentTranslateDb()->dev_description                      = $this->devDescription;
        $this->getCurrentTranslateDb()->admin_name                           = $this->adminName;
        $this->getCurrentTranslateDb()->admin_description                    = $this->adminDescription;
        $this->getCurrentTranslateDb()->common_language_id                   = $this->language->id;
        $this->getCurrentTranslateDb()->input_condition_template_template_id = $this->inputConditionTemplate->id;

        return $this->getCurrentTranslateDb()->save();
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->inputConditionTemplate) return false;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTranslateDb()
    {
        if ($this->currentTranslateDb) return $this->currentTranslateDb;

        $this->currentTranslateDb = InputConditionsNamesTranslatesDb::find()
            ->where([
                'common_language_id'                   => $this->language->id,
                'input_condition_template_template_id' => $this->inputConditionTemplate->id,
            ])
            ->one();

        if (!$this->currentTranslateDb)
            $this->createTranslateDb();
        else {
            $this->devName          = $this->currentTranslateDb->dev_name;
            $this->devDescription   = $this->currentTranslateDb->dev_description;
            $this->adminName        = $this->currentTranslateDb->admin_name;
            $this->adminDescription = $this->currentTranslateDb->admin_description;
        }

        return $this->currentTranslateDb;
    }

    /**
     * @inheritdoc
     */
    protected function createTranslateDb()
    {
        $this->currentTranslateDb = new InputConditionsNamesTranslatesDb();
        $this->currentTranslateDb->common_language_id                   = $this->language->id;
        $this->currentTranslateDb->input_condition_template_template_id = $this->inputConditionTemplate->id;

        return $this->currentTranslateDb->save();
    }
}
