<?php

namespace Iliich246\YicmsFeedback\InputImages;

use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
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
    public $inputFilesBlock;
    /** @var InputImageNamesTranslatesForm[] */
    public $filesNameTranslates;
    /** @var bool indicate that data in this group was saved in this action */
    public $justSaved = false;

    /**
     * Sets inputImageTemplateReference
     * @param $inputImageTemplateReference
     */
    public function setInputFilesTemplateReference($inputImageTemplateReference)
    {
        $this->inputImageTemplateReference = $inputImageTemplateReference;
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
        throw new FeedbackException('Not implemented for developer input images group (not necessary)');
    }
}