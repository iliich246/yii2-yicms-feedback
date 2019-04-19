<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class ImagesInputHandler
 *
 * Object of this class must aggregate any object, that must implement input images functionality.
 *
 * @property ImageInputReferenceInterface|NonexistentInterface|FictiveInterface|AnnotatorFileInterface $aggregator
 */
class ImagesInputHandler extends AbstractHandler
{
    /**
     * ImagesInputHandler constructor.
     * @param ImageInputReferenceInterface $aggregator
     */
    public function __construct(ImageInputReferenceInterface $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Return instance of input image by name
     * @param $name
     * @return InputImage
     */
    public function getInputImageBlock($name)
    {
        if ($this->aggregator->isNonexistent()) {
            $nonexistentInputImage = new InputImagesBlock();
            $nonexistentInputImage->setNonexistent();
            $nonexistentInputImage->setNonexistentName($name);

            return $nonexistentInputImage;
        }

        if (!$this->aggregator->isFictive()) return $this->forRealImageBlock($name);

        return $this->forFictiveImageBlock($name);
    }

    /**
     * Returns true if aggregator has input image block with name
     * @param $name
     * @return bool
     */
    public function isInputImageBlock($name)
    {
        if ($this->aggregator->isNonexistent()) return false;

        if (!$this->aggregator->isAnnotationActive())
            return InputImagesBlock::isTemplate($this->aggregator->getInputImageTemplateReference(), $name);

        /** @var InputImagesBlock $className */
        $className = $this->getClassName($name);

        if (class_exists($className))
            return $className::isTemplate($this->aggregator->getInputImageTemplateReference(), $name);

        return InputImagesBlock::isTemplate($this->aggregator->getInputImageTemplateReference(), $name);
    }

    /**
     * Makes fictive input image block
     * @param $name
     * @return bool|object
     */
    private function forFictiveImageBlock($name)
    {
        return $this->getOrSet($name, function() use($name) {
            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    /** @var InputImagesBlock $template */
                    $template =  InputImagesBlock::getInstance($this->aggregator->getInputImageTemplateReference(), $name);

                    if (!$template) {
                        $nonexistentInputImage = new InputImagesBlock();
                        $nonexistentInputImage->setNonexistent();
                        $nonexistentInputImage->setNonexistentName($name);

                        return $nonexistentInputImage;
                    }

                    $template->setFictive();
                    $template->scenario = InputImage::SCENARIO_CREATE;

                    return $template;
                }

                /** @var InputImagesBlock $className */
                $className = $this->getClassName($name);

                if (class_exists($className)) {
                    /** @var InputImagesBlock $template */
                    $template =  $className::getInstance($this->aggregator->getInputImageTemplateReference(), $name);

                    if (!$template) {
                        $nonexistentInputImage = new InputImagesBlock();
                        $nonexistentInputImage->setNonexistent();
                        $nonexistentInputImage->setNonexistentName($name);
                        $template->setParentFileAnnotator($this->aggregator);

                        return $nonexistentInputImage;
                    }

                    $template->setFictive();
                    $template->scenario = InputImage::SCENARIO_CREATE;

                    return $template;
                }
            }

            /** @var InputImagesBlock $template */
            $template =  InputImagesBlock::getInstance($this->aggregator->getInputImageTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputImage = new InputImagesBlock();
                $nonexistentInputImage->setNonexistent();
                $nonexistentInputImage->setNonexistentName($name);

                return $nonexistentInputImage;
            }

            $template->setFictive();
            $template->scenario = InputImage::SCENARIO_CREATE;

            return $template;
        });
    }

    /**
     * Return instance of input image for real image block
     * @param $name
     * @return bool|object
     */
    private function forRealImageBlock($name)
    {
        return $this->getOrSet($name, function() use($name) {
            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    $inputImageBlock = InputImagesBlock::getInstance(
                        $this->aggregator->getInputImageTemplateReference(),
                        $name,
                        $this->aggregator->getInputImageReference()
                    );

                    $inputImageBlock->scenario = InputImage::SCENARIO_UPDATE;

                    return $inputImageBlock;
                }

                /** @var InputImagesBlock $className */
                $className = $this->getClassName($name);

                if (class_exists($className)) {
                    $inputImageBlock = $className::getInstance(
                        $this->aggregator->getInputImageTemplateReference(),
                        $name,
                        $this->aggregator->getInputImageReference()
                    );
                } else {
                    $inputImageBlock = InputImagesBlock::getInstance(
                        $this->aggregator->getInputImageTemplateReference(),
                        $name,
                        $this->aggregator->getInputImageReference()
                    );
                }

                $inputImageBlock->scenario = InputImage::SCENARIO_UPDATE;
                $inputImageBlock->setParentFileAnnotator($this->aggregator);

                return $inputImageBlock;
            }
            $inputImageBlock = InputImagesBlock::getInstance(
                $this->aggregator->getInputImageTemplateReference(),
                $name,
                $this->aggregator->getInputImageReference()
            );

            $inputImageBlock->scenario = InputImage::SCENARIO_UPDATE;
            $inputImageBlock->setParentFileAnnotator($this->aggregator);

            return $inputImageBlock;
        });
    }

    /**
     * Return class name for annotated images block
     * @param $name
     * @return string
     */
    private function getClassName($name)
    {
        return $this->aggregator->getAnnotationFileNamespace() . '\\' .
               $this->aggregator->getAnnotationFileName() . '\\InputImages\\' .
               ucfirst(mb_strtolower($name)) . 'InputImagesBlock';
    }
}
