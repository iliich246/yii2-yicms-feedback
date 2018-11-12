<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Iliich246\YicmsCommon\Files\FileNamesTranslatesForm;
use Iliich246\YicmsFeedback\Base\FeedbackException;
use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsCommon\Base\CommonException;

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
    /** @var FileNamesTranslatesForm[] */
    public $filesNameTranslates;
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
     */
    public function initialize($inputFilesBlockId = null)
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
        throw new FeedbackException('Not implemented for developer input files group (not necessary)');
    }
}
