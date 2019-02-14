<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsCommon\Languages\Language;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class DevInputImagesGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class DevInputImagesGroup extends AbstractGroup
{
    /** @var string inputImageTemplateReference value for current group */
    protected $inputImageTemplateReference;
    /** @var InputImagesBlock current input image block with group is working (create or update) */
    public $inputImagesBlock;
    /** @var InputImageNamesTranslatesForm[] */
    public $inputImagesNameTranslates;
    /** @var bool indicate that data in this group was saved in this action */
    public $justSaved = false;

    /**
     * Sets inputImageTemplateReference
     * @param $inputImageTemplateReference
     */
    public function setInputImagesTemplateReference($inputImageTemplateReference)
    {
        $this->inputImageTemplateReference = $inputImageTemplateReference;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     * @throws \Iliich246\YicmsCommon\Base\CommonException
     */
    public function initialize($inputImagesBlockId = null)
    {
        if (!$inputImagesBlockId) {
            $this->inputImagesBlock                                 = new InputImagesBlock();
            $this->inputImagesBlock->input_image_template_reference = $this->inputImageTemplateReference;
            $this->inputImagesBlock->scenario                       = InputImagesBlock::SCENARIO_CREATE;
            $this->scenario                                         = self::SCENARIO_CREATE;
        } else {
            $this->inputImagesBlock = InputImagesBlock::getInstanceById($inputImagesBlockId);

            if (!$this->inputImagesBlock)  throw new FeedbackException("Wrong inputImagesBlockId = $inputImagesBlockId");

            $this->inputImagesBlock->scenario = InputImagesBlock::SCENARIO_UPDATE;
            $this->scenario = self::SCENARIO_UPDATE;
        }

        $languages = Language::getInstance()->usedLanguages();

        $this->inputImagesNameTranslates = [];

        foreach($languages as $key => $language) {

            $inputFileNameTranslates = new InputImageNamesTranslatesForm();
            $inputFileNameTranslates->setLanguage($language);
            $inputFileNameTranslates->setInputImageBlock($this->inputImagesBlock);

            if (!$this->inputImagesBlock->isNewRecord)
                $inputFileNameTranslates->loadFromDb();

            $this->inputImagesNameTranslates[$key] = $inputFileNameTranslates;
        }

    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return ($this->inputImagesBlock->validate() && Model::validateMultiple($this->inputImagesNameTranslates));
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        return $this->inputImagesBlock->load($data) && Model::loadMultiple($this->inputImagesNameTranslates, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $needSaveInputImageBlock = false;

        if (!$needSaveInputImageBlock &&
            $this->inputImagesBlock->getOldAttribute('program_name') != $this->inputImagesBlock->program_name)
            $needSaveInputImageBlock = true;

        if (!$needSaveInputImageBlock &&
            $this->inputImagesBlock->getOldAttribute('active') != $this->inputImagesBlock->active)
            $needSaveInputImageBlock = true;

        if (!$needSaveInputImageBlock &&
            $this->inputImagesBlock->getOldAttribute('editable') != $this->inputImagesBlock->editable)
            $needSaveInputImageBlock = true;

        if ($needSaveInputImageBlock)
            $this->inputImagesBlock->save(false);

        /** @var InputImageNamesTranslatesForm $imageNameTranslate */
        foreach($this->inputImagesNameTranslates as $inputImagesNameTranslate) {
            $needSaveInputInputTemplateName = false;

            if (!$needSaveInputInputTemplateName &&
                $inputImagesNameTranslate->devName != $inputImagesNameTranslate->getCurrentTranslateDb()->dev_name)
                $needSaveInputInputTemplateName = true;

            if (!$needSaveInputInputTemplateName &&
                $inputImagesNameTranslate->devDescription != $inputImagesNameTranslate->getCurrentTranslateDb()->dev_description)
                $needSaveInputInputTemplateName = true;

            if (!$needSaveInputInputTemplateName &&
                $inputImagesNameTranslate->adminName != $inputImagesNameTranslate->getCurrentTranslateDb()->admin_name)
                $needSaveInputInputTemplateName = true;

            if (!$needSaveInputInputTemplateName &&
                $inputImagesNameTranslate->adminDescription != $inputImagesNameTranslate->getCurrentTranslateDb()->admin_description)
                $needSaveInputInputTemplateName = true;

            if ($needSaveInputInputTemplateName)
                $inputImagesNameTranslate->save();
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
        throw new FeedbackException('Not implemented for developer input images group (not necessary)');
    }
}
