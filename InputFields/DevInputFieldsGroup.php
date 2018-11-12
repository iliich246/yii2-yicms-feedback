<?php

namespace Iliich246\YicmsFeedback\InputFields;

use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsCommon\Base\CommonException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class DevInputFieldsGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DevInputFieldsGroup extends AbstractGroup
{
    /** @var string inputFieldTemplateReference value for current group */
    protected $inputFieldTemplateReference;
    /** @var InputFieldTemplate current input field template with group is working (create or update) */
    public $inputFieldTemplate;
    /** @var InputFieldNamesTranslatesForm[] */
    public $fieldNameTranslates;
    /** @var InputFieldTemplate[] array associated with object with current $fieldTemplateReference */
    public $inputFieldTemplatesSingle;
    /** @var bool indicate that data in this group was saved in this action */
    public $justSaved = false;

    /**
     * Sets inputFieldTemplateReference
     * @param integer $inputFieldTemplateReference
     */
    public function setInputFieldTemplateReference($inputFieldTemplateReference)
    {
        $this->inputFieldTemplateReference = $inputFieldTemplateReference;
    }

    /**
     * Sets update scenario
     */
    public function setUpdateScenario()
    {
        $this->scenario = self::SCENARIO_UPDATE;
        $this->inputFieldTemplate->scenario = InputFieldTemplate::SCENARIO_UPDATE;
    }

    /**
     * @inheritdoc
     * @throws CommonException
     * @throws FeedbackException
     */
    public function initialize($inputFieldTemplateId = null)
    {
        if (!$inputFieldTemplateId) {
            $this->inputFieldTemplate = new InputFieldTemplate();
            $this->inputFieldTemplate->input_field_template_reference = $this->inputFieldTemplateReference;
            $this->inputFieldTemplate->scenario = InputFieldTemplate::SCENARIO_CREATE;
            $this->scenario = self::SCENARIO_CREATE;
        } else {
            $this->inputFieldTemplate = InputFieldTemplate::getInstanceById($inputFieldTemplateId);

            if (!$this->inputFieldTemplate) throw new FeedbackException("Wrong inputFieldTemplateId = $inputFieldTemplateId");

            $this->inputFieldTemplate->scenario = InputFieldTemplate::SCENARIO_UPDATE;
            $this->scenario = self::SCENARIO_UPDATE;
        }

        $languages = Language::getInstance()->usedLanguages();

        $this->fieldNameTranslates = [];

        foreach($languages as $key => $language) {

            $fieldNameTranslate = new InputFieldNamesTranslatesForm();
            $fieldNameTranslate->setLanguage($language);
            $fieldNameTranslate->setInputFieldTemplate($this->inputFieldTemplate);

            if (!$this->inputFieldTemplate->isNewRecord)
                $fieldNameTranslate->loadFromDb();

            $this->fieldNameTranslates[$key] = $fieldNameTranslate;
        }
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return ($this->inputFieldTemplate->validate() && Model::validateMultiple($this->fieldNameTranslates));
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return $this->inputFieldTemplate->load($data) && Model::loadMultiple($this->fieldNameTranslates, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $needSaveFieldTemplate = false;

        if (!$needSaveFieldTemplate &&
            $this->inputFieldTemplate->getOldAttribute('program_name') != $this->inputFieldTemplate->program_name)
            $needSaveFieldTemplate = true;

        if (!$needSaveFieldTemplate &&
            $this->inputFieldTemplate->getOldAttribute('visible') != $this->inputFieldTemplate->visible)
            $needSaveFieldTemplate = true;

        if (!$needSaveFieldTemplate &&
            $this->inputFieldTemplate->getOldAttribute('editable') != $this->inputFieldTemplate->editable)
            $needSaveFieldTemplate = true;

        if ($needSaveFieldTemplate)
            $this->inputFieldTemplate->save(false);

        /** @var InputFieldNamesTranslatesForm $fieldNameTranslate */
        foreach($this->fieldNameTranslates as $fieldNameTranslate) {

            $needSaveFieldTemplateName = false;

            if (!$needSaveFieldTemplateName &&
                $fieldNameTranslate->devName != $fieldNameTranslate->getCurrentTranslateDb()->dev_name)
                $needSaveFieldTemplateName = true;

            if (!$needSaveFieldTemplateName &&
                $fieldNameTranslate->devDescription != $fieldNameTranslate->getCurrentTranslateDb()->dev_description)
                $needSaveFieldTemplateName = true;

            if (!$needSaveFieldTemplateName &&
                $fieldNameTranslate->adminName != $fieldNameTranslate->getCurrentTranslateDb()->admin_name)
                $needSaveFieldTemplateName = true;

            if (!$needSaveFieldTemplateName &&
                $fieldNameTranslate->adminDescription != $fieldNameTranslate->getCurrentTranslateDb()->admin_description)
                $needSaveFieldTemplateName = true;

            if ($needSaveFieldTemplateName)
                $fieldNameTranslate->save();
        }

        $this->justSaved = true;

        //TODO: makes error handling
        return true;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function render(ActiveForm $form)
    {
        throw new FeedbackException('Not implemented for developer input fields group (not necessary)');
    }
}
