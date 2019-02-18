<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class ImagesInputHandler
 *
 * Object of this class must aggregate any object, that must implement input images functionality.
 *
 * @property ImageInputReferenceInterface|NonexistentInterface|FictiveInterface $aggregator
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
            $nonexistentInputImage = new InputImage();
            $nonexistentInputImage->setNonexistent();
            $nonexistentInputImage->setNonexistentName($name);

            return $nonexistentInputImage;
        }

        if (!$this->aggregator->isFictive()) return $this->forRealImage($name);

        return $this->forFictiveImage($name);
    }

    /**
     * Makes fictive input image
     * @param $name
     * @return bool|object
     */
    private function forFictiveImage($name)
    {
        return $this->getOrSet($name, function() use($name) {
            /** @var InputImagesBlock $template */
            $template =  InputImagesBlock::getInstance($this->aggregator->getInputImageTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputImage = new InputImage();
                $nonexistentInputImage->setNonexistent();
                $nonexistentInputImage->setNonexistentName($name);

                return $nonexistentInputImage;
            }

            $fictiveImage = new InputImage();
            $fictiveImage->setFictive();
            $fictiveImage->setEntityBlock($template);
            $fictiveImage->scenario = InputImage::SCENARIO_CREATE;

            return $fictiveImage;
        });
    }

    /**
     * Return instance of input image for real image
     * @param $name
     * @return bool|object
     */
    private function forRealImage($name)
    {
        return $this->getOrSet($name, function() use($name) {
            $inputImage = InputImagesBlock::getInstance(
                $this->aggregator->getInputImageTemplateReference(),
                $name,
                $this->aggregator->getInputImageReference()
            );

            $inputImage->scenario = InputImage::SCENARIO_UPDATE;

            return $inputImage;
        });
    }
}
