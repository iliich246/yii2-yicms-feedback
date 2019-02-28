<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Iliich246\YicmsCommon\Base\AbstractTranslateForm;

/**
 * Class InputFieldNamesTranslatesForm
 *
 * @property InputFieldsNamesTranslatesDb $currentTranslateDb
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFieldNamesTranslatesForm extends AbstractTranslateForm
{
    /** @var string name of page in current model language */
    public $devName;
    /** @var string description of page on current model language */
    public $devDescription;
    /** @var string name of page in current model language */
    public $adminName;
    /** @var string description of page on current model language */
    public $adminDescription;
    /** @var InputFieldTemplate associated with this model */
    private $inputFieldTemplate;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'devName'          => 'Admin input field name on language "' . $this->language->name . '"',
            'devDescription'   => 'Admin description of input field on language "' . $this->language->name . '"',
            'adminName'        => 'User input field name on language "' . $this->language->name . '"',
            'adminDescription' => 'User description of field on language "' . $this->language->name . '"',
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
        return '@yicms-feedback/Views/translates/input_field_name_translate';
    }

    /**
     * Sets InputFieldTemplate associated with this object
     * @param InputFieldTemplate $inputFieldTemplate
     */
    public function setInputFieldTemplate(InputFieldTemplate $inputFieldTemplate)
    {
        $this->inputFieldTemplate = $inputFieldTemplate;
    }

    /**
     * Saves record in data base
     * @return bool
     */
    public function save()
    {
        $this->getCurrentTranslateDb()->dev_name                          = $this->devName;
        $this->getCurrentTranslateDb()->dev_description                   = $this->devDescription;
        $this->getCurrentTranslateDb()->admin_name                        = $this->adminName;
        $this->getCurrentTranslateDb()->admin_description                 = $this->adminDescription;
        $this->getCurrentTranslateDb()->common_language_id                = $this->language->id;
        $this->getCurrentTranslateDb()->feedback_input_fields_template_id = $this->inputFieldTemplate->id;

        return $this->getCurrentTranslateDb()->save();
    }

    /**
     * @inheritdoc
     */
    protected function isCorrectConfigured()
    {
        if (!parent::isCorrectConfigured() || !$this->inputFieldTemplate) return false;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCurrentTranslateDb()
    {
        if ($this->currentTranslateDb) return $this->currentTranslateDb;

        $this->currentTranslateDb = InputFieldsNamesTranslatesDb::find()
            ->where([
                'common_language_id'                => $this->language->id,
                'feedback_input_fields_template_id' => $this->inputFieldTemplate->id,
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
        $this->currentTranslateDb = new InputFieldsNamesTranslatesDb();
        $this->currentTranslateDb->common_language_id                = $this->language->id;
        $this->currentTranslateDb->feedback_input_fields_template_id = $this->inputFieldTemplate->id;

        return $this->currentTranslateDb->save();
    }
}
