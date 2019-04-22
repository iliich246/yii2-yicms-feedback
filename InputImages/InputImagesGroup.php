<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;
use yii\helpers\FileHelper;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
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
    /** @var ImageInputReferenceInterface|ImageInputInterface|AnnotatorFileInterface inputImageTemplateReference value for current group */
    protected $imageInputReference;
    /** @var InputImagesBlock[] for working with forms */
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

        /** @var InputImagesBlock[] $annotatedImagesBlocks */
        $annotatedImagesBlocks = [];

        foreach($inputImagesBlocks as $inputImagesBlock) {
            $className = $this->imageInputReference->getAnnotationFileNamespace() . '\\' .
                $this->imageInputReference->getAnnotationFileName() . '\\InputImages\\' .
                ucfirst(mb_strtolower($inputImagesBlock->program_name)) . 'InputImagesBlock';

            if (class_exists($className)) {
                $annotatedImagesBlock = $this->imageInputReference
                                            ->getInputImagesHandler()
                                            ->getInputImageBlock($inputImagesBlock->program_name);
                $annotatedImagesBlocks[] = $annotatedImagesBlock;
            } else {
                $annotatedImagesBlocks[] = $inputImagesBlock;
            }
        }

        foreach($annotatedImagesBlocks as $inputImagesBlock) {
            $inputImagesBlock->prepareValidators();
            $this->inputImages["$inputImagesBlock->id"] = $inputImagesBlock;
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

        if (!InputImagesBlock::isLoadedMultiple($this->inputImages)) {
            $result = '';

            foreach($this->inputImages as $inputImages)
                if (!$inputImages->isLoaded())
                    $result .= '"' . $inputImages->program_name . '", ';

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

            $inputImage->currentInputImageReference = $this->imageInputReference->getInputImageReference();
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
