<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class DevInputFilesGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DevInputFilesGroup extends AbstractGroup
{
    /** @var string inputFileTemplateReference value for current group */
    protected $inputFileTemplateReference;
    /** @var InputFilesBlock current input field template with group is working (create or update) */
    public $inputFilesBlock;
    /** @var InputFileNamesTranslatesForm[] */
    public $inputFilesNameTranslates;
    /** @var bool indicate that data in this group was saved in this action */
    public $justSaved = false;

    /**
     * Sets inputFileTemplateReference
     * @param $inputFileTemplateReference
     */
    public function setInputFilesTemplateReference($inputFileTemplateReference)
    {
        $this->inputFileTemplateReference = $inputFileTemplateReference;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function initialize($inputFilesBlockId = null)
    {
        if (!$inputFilesBlockId) {
            $this->inputFilesBlock                                = new InputFilesBlock();
            $this->inputFilesBlock->input_file_template_reference = $this->inputFileTemplateReference;
            $this->inputFilesBlock->scenario                      = InputFilesBlock::SCENARIO_CREATE;
            $this->scenario                                       = self::SCENARIO_CREATE;
        } else {
            $this->inputFilesBlock = InputFilesBlock::getInstanceById($inputFilesBlockId);

            if (!$this->inputFilesBlock)  throw new FeedbackException("Wrong inputFilesBlockId = $inputFilesBlockId");

            $this->inputFilesBlock->scenario = InputFilesBlock::SCENARIO_UPDATE;
            $this->scenario                  = self::SCENARIO_UPDATE;
        }

        $languages = Language::getInstance()->usedLanguages();
        
        $this->inputFilesNameTranslates = [];

        foreach($languages as $key => $language) {

            $inputFileNameTranslates = new InputFileNamesTranslatesForm();
            $inputFileNameTranslates->setLanguage($language);
            $inputFileNameTranslates->setInputFileTemplate($this->inputFilesBlock);

            if (!$this->inputFilesBlock->isNewRecord)
                $inputFileNameTranslates->loadFromDb();

            $this->inputFilesNameTranslates[$key] = $inputFileNameTranslates;
        }
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return ($this->inputFilesBlock->validate() && Model::validateMultiple($this->inputFilesNameTranslates));
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return $this->inputFilesBlock->load($data) && Model::loadMultiple($this->inputFilesNameTranslates, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $needSaveInputFileBlock = false;

        if (!$needSaveInputFileBlock &&
            $this->inputFilesBlock->getOldAttribute('program_name') != $this->inputFilesBlock->program_name)
            $needSaveInputFileBlock = true;

        if (!$needSaveInputFileBlock &&
            $this->inputFilesBlock->getOldAttribute('active') != $this->inputFilesBlock->active)
            $needSaveInputFileBlock = true;

        if (!$needSaveInputFileBlock &&
            $this->inputFilesBlock->getOldAttribute('editable') != $this->inputFilesBlock->editable)
            $needSaveInputFileBlock = true;

        if ($needSaveInputFileBlock)
            $this->inputFilesBlock->save(false);

        /** @var InputFileNamesTranslatesForm $filesNameTranslate */
        foreach($this->inputFilesNameTranslates as $inputFilesNameTranslate) {
            $needSaveInputFileTemplateName = false;

            if (!$needSaveInputFileTemplateName &&
                $inputFilesNameTranslate->devName != $inputFilesNameTranslate->getCurrentTranslateDb()->dev_name)
                $needSaveInputFileTemplateName = true;

            if (!$needSaveInputFileTemplateName &&
                $inputFilesNameTranslate->devDescription != $inputFilesNameTranslate->getCurrentTranslateDb()->dev_description)
                $needSaveInputFileTemplateName = true;

            if (!$needSaveInputFileTemplateName &&
                $inputFilesNameTranslate->adminName != $inputFilesNameTranslate->getCurrentTranslateDb()->admin_name)
                $needSaveInputFileTemplateName = true;

            if (!$needSaveInputFileTemplateName &&
                $inputFilesNameTranslate->adminDescription != $inputFilesNameTranslate->getCurrentTranslateDb()->admin_description)
                $needSaveInputFileTemplateName = true;

            if ($needSaveInputFileTemplateName)
                $inputFilesNameTranslate->save();
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
        throw new FeedbackException('Not implemented for developer input files group (not necessary)');
    }
}
