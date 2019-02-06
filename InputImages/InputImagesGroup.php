<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsFeedback\Base\FeedbackException;

/**
 * Class InputImagesGroup
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImagesGroup extends AbstractGroup
{
    /** @var ImageInputReferenceInterface|ImageInputInterface inputImageTemplateReference value for current group */
    protected $imageInputReference;
    /** @var InputImage[] for working with forms */
    public $inputImages;

    /**
     * Sets imageInputReference object for this
     * @param ImageInputReferenceInterface $imageInputReference
     */
    public function setImageInputReference(ImageInputReferenceInterface $imageInputReference)
    {
        $this->imageInputReference = $imageInputReference;
    }

    /**
     * @inheritdoc
     */
    public function initialize($inputImageTemplateId = null)
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
        throw new FeedbackException('Not implemented for developer input image group (not necessary)');
    }
}
