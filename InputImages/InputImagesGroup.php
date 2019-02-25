<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;
use yii\helpers\FileHelper;
use Iliich246\YicmsCommon\Base\AbstractGroup;
use Iliich246\YicmsFeedback\FeedbackModule;
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
        /** @var InputImagesBlock[] $inputImagesBlocks */
        $inputImagesBlocks = InputImagesBlock::find()->where([
            'input_image_template_reference' => $this->imageInputReference->getInputImageTemplateReference(),
            'active'                         => true
        ])->all();

        foreach($inputImagesBlocks as $inputImagesBlock) {
            $inputImage = $this
                ->imageInputReference
                ->getInputImagesHandler()
                ->getInputImageBlock($inputImagesBlock->program_name);

            $inputImage->prepareValidators();
            $this->inputImages["$inputImagesBlock->id"] = $inputImage;
        }

        return $this->inputImages;
    }

    /**
     * Returns true if this group has active input images
     * @return bool
     */
    public function isActiveInputImages()
    {
        return !!count($this->inputImages);
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        if (!$this->inputImages) return true;

        if (!InputImage::isLoadedMultiple($this->inputImages)) {
            $result = '';

            foreach($this->inputImages as $inputImages)
                if (!$inputImages->isLoaded())
                    $result .= '"' . $inputImages->getInputImagesBlock()->program_name . '", ';

            $result = substr($result , 0, -2);

            Yii::error(
                'In feedback form don`t used next active input images: ' .
                $result,  __METHOD__);

            if (defined('YICMS_STRICT')) {
                throw new FeedbackException('In feedback form don`t used next active input images: ' .
                    $result);
            }

            return false;
        }

        return Model::validateMultiple($this->inputImages);
    }

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        if (!$this->inputImages) return true;

        return Model::loadMultiple($this->inputImages, $data);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if (!$this->inputImages) return false;

        $path = FeedbackModule::getInstance()->inputImagesPath;

        if (!is_dir($path))
            FileHelper::createDirectory($path);

        $success = true;

        foreach($this->inputImages as $inputImage) {

            $inputImage->input_image_reference = $this->imageInputReference->getInputImageReference();
            $success = $inputImage->saveInputImage();

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
        throw new FeedbackException('Not implemented for developer input image group (not necessary)');
    }
}
