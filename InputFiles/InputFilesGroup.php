<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
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
    public $inputFields;

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

    }

    /**
     * @inheritdoc
     */
    public function validate()
    {

    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {

    }

    /**
     * @inheritdoc
     */
    public function save()
    {

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
