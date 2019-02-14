<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsCommon\Base\CommonException;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class DevInputConditionsGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DevInputConditionsGroup extends AbstractGroup
{
    /** @var string inputConditionTemplateReference value for current group */
    protected $inputConditionTemplateReference;
    /** @var InputConditionTemplate current input condition template with group is working (create or update) */
    public $inputConditionTemplate;
    /** @var InputConditionNamesTranslatesForm[] */
    public $conditionNameTranslates;
    /** @var bool indicate that data in this group was saved in this action */
    public $justSaved = false;

    /**
     * Sets inputConditionTemplateReference
     * @param $inputConditionTemplateReference
     */
    public function setInputConditionsTemplateReference($inputConditionTemplateReference)
    {
        $this->inputConditionTemplateReference = $inputConditionTemplateReference;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function initialize($inputConditionTemplateId = null)
    {
        if (!$inputConditionTemplateId) {
            $this->inputConditionTemplate                                     = new InputConditionTemplate();
            $this->inputConditionTemplate->input_condition_template_reference = $this->inputConditionTemplateReference;
            $this->inputConditionTemplate->scenario                           = InputConditionTemplate::SCENARIO_CREATE;
            $this->scenario                                                   = self::SCENARIO_CREATE;
        } else {
            $this->inputConditionTemplate = InputConditionTemplate::getInstanceById($inputConditionTemplateId);

            if (!$this->inputConditionTemplate)  throw new FeedbackException("Wrong inputConditionTemplateId = $inputConditionTemplateId");

            $this->inputConditionTemplate->scenario = InputConditionTemplate::SCENARIO_UPDATE;
            $this->scenario                         = self::SCENARIO_UPDATE;
        }

        $languages = Language::getInstance()->usedLanguages();

        $this->conditionNameTranslates = [];

        foreach($languages as $key => $language) {

            $inputFileNameTranslates = new InputConditionNamesTranslatesForm();
            $inputFileNameTranslates->setLanguage($language);
            $inputFileNameTranslates->setInputConditionTemplate($this->inputConditionTemplate);

            if (!$this->inputConditionTemplate->isNewRecord)
                $inputFileNameTranslates->loadFromDb();

            $this->conditionNameTranslates[$key] = $inputFileNameTranslates;
        }
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return ($this->inputConditionTemplate->validate() && Model::validateMultiple($this->conditionNameTranslates));
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return $this->inputConditionTemplate->load($data) && Model::loadMultiple($this->conditionNameTranslates, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $needSaveInputConditionTemplate = false;

        if (!$needSaveInputConditionTemplate &&
            $this->inputConditionTemplate->getOldAttribute('program_name') != $this->inputConditionTemplate->program_name)
            $needSaveInputConditionTemplate = true;

        if (!$needSaveInputConditionTemplate &&
            $this->inputConditionTemplate->getOldAttribute('active') != $this->inputConditionTemplate->active)
            $needSaveInputConditionTemplate = true;

        if (!$needSaveInputConditionTemplate &&
            $this->inputConditionTemplate->getOldAttribute('editable') != $this->inputConditionTemplate->editable)
            $needSaveInputConditionTemplate = true;

        if (!$needSaveInputConditionTemplate &&
            $this->inputConditionTemplate->getOldAttribute('checkbox_state_default') != $this->inputConditionTemplate->checkbox_state_default)
            $needSaveInputConditionTemplate = true;

        if (!$needSaveInputConditionTemplate &&
            $this->inputConditionTemplate->getOldAttribute('type') != $this->inputConditionTemplate->type)
            $needSaveInputConditionTemplate = true;

        if ($needSaveInputConditionTemplate)
            $this->inputConditionTemplate->save(false);

        /** @var InputConditionNamesTranslatesForm $conditionsNameTranslate */
        foreach($this->conditionNameTranslates as $inputConditionsNameTranslate) {
            $needSaveInputConditionTemplateName = false;

            \Yii::error(print_r($inputConditionsNameTranslate,true));

            if (!$needSaveInputConditionTemplateName &&
                $inputConditionsNameTranslate->devName != $inputConditionsNameTranslate->getCurrentTranslateDb()->dev_name)
                $needSaveInputConditionTemplateName = true;

            if (!$needSaveInputConditionTemplateName &&
                $inputConditionsNameTranslate->devDescription != $inputConditionsNameTranslate->getCurrentTranslateDb()->dev_description)
                $needSaveInputConditionTemplateName = true;

            if (!$needSaveInputConditionTemplateName &&
                $inputConditionsNameTranslate->adminName != $inputConditionsNameTranslate->getCurrentTranslateDb()->admin_name)
                $needSaveInputConditionTemplateName = true;

            if (!$needSaveInputConditionTemplateName &&
                $inputConditionsNameTranslate->adminDescription != $inputConditionsNameTranslate->getCurrentTranslateDb()->admin_description)
                $needSaveInputConditionTemplateName = true;

            if ($needSaveInputConditionTemplateName)
                $inputConditionsNameTranslate->save();
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
        throw new FeedbackException('Not implemented for developer input conditions group (not necessary)');
    }
}
