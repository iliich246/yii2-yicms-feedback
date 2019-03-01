<?php

namespace Iliich246\YicmsFeedback\InputFiles;


use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsFeedback\FeedbackModule;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputFilesGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFilesGroup extends AbstractGroup
{
    /** @var FileInputReferenceInterface|FileInputInterface inputFileTemplateReference value for current group */
    protected $fileInputReference;
    /** @var InputFile[] for working with forms */
    public $inputFiles;

    /**
     * Sets fileInputReference object for this
     * @param FileInputReferenceInterface $fileInputReference
     */
    public function setFileInputReference(FileInputReferenceInterface $fileInputReference)
    {
        $this->fileInputReference = $fileInputReference;
    }

    /**
     * @inheritdoc
     */
    public function initialize($inputFieldTemplateId = null)
    {
        /** @var InputFilesBlock[] $inputFilesBlocks */
        $inputFilesBlocks = InputFilesBlock::find()->where([
            'input_file_template_reference' => $this->fileInputReference->getInputFileTemplateReference(),
            'active'                        => true,
        ])->all();

        foreach($inputFilesBlocks as $inputFilesBlock) {
            $inputFilesBlock->prepareValidators();
            $this->inputFiles["$inputFilesBlock->id"] = $inputFilesBlock;
        }

        return $this->inputFiles;
    }

    /**
     * Returns true if this group has active input files
     * @return bool
     */
    public function isActiveInputFiles()
    {
        return !!count($this->inputFiles);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (!$this->inputFiles) return true;

        if (!InputFile::isLoadedMultiple($this->inputFiles)) {
            $result = '';

            foreach($this->inputFiles as $inputFile)
                if (!$inputFile->isLoaded())
                    $result .= '"' . $inputFile->getInputFileBlock()->program_name . '", ';

            $result = substr($result , 0, -2);

            Yii::error(
                'In feedback form don`t used next active input fields: ' .
                $result,  __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException('In feedback form don`t used next active input fields: ' .
                    $result);
            }

            return false;
        }

        return Model::validateMultiple($this->inputFiles);
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        if (!$this->inputFiles) return true;

        return Model::loadMultiple($this->inputFiles, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if (!$this->inputFiles) return false;

        $path = FeedbackModule::getInstance()->inputFilesPatch;

        if (!is_dir($path))
            FileHelper::createDirectory($path);

        $success = true;

        foreach($this->inputFiles as $inputFile) {

            $inputFile->input_file_reference = $this->fileInputReference->getInputFileReference();
            $success = $inputFile->saveInputFile();

            if (!$success) return false;
        }

        return $success;
    }

    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function render(ActiveForm $form)
    {
        throw new FeedbackException('Not implemented for developer input file group (not necessary)');
    }
}
