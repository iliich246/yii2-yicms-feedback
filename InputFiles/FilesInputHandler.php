<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class FilesInputHandler
 *
 * Object of this class must aggregate any object, that must implement input files functionality.
 *
 * @property FileInputReferenceInterface|NonexistentInterface|FictiveInterface $aggregator
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FilesInputHandler extends AbstractHandler
{
    /**
     * FilesInputHandler constructor.
     * @param FileInputReferenceInterface $aggregator
     */
    public function __construct(FileInputReferenceInterface $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * Return instance of input file by name
     * @param $name
     * @return InputFile
     */
    public function getInputFileBlock($name)
    {
        if ($this->aggregator->isNonexistent()) {
            $nonexistentInputFileBlock = new InputFilesBlock();
            $nonexistentInputFileBlock->setNonexistent();
            $nonexistentInputFileBlock->setNonexistentName($name);

            return $nonexistentInputFileBlock;
        }

        if (!$this->aggregator->isFictive()) return $this->forRealFileBlock($name);

        return $this->forFictiveFileBlock($name);
    }

    /**
     * Makes fictive input file
     * @param $name
     * @return bool|object
     */
    private function forFictiveFileBlock($name)
    {
        return $this->getOrSet($name, function() use($name) {
            /** @var InputFilesBlock $template */
            $template = InputFilesBlock::getInstance($this->aggregator->getInputFileTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputFileBlock = new InputFilesBlock();
                $nonexistentInputFileBlock->setNonexistent();
                $nonexistentInputFileBlock->setNonexistentName($name);

                return $nonexistentInputFileBlock;
            }

            //$fictiveFileBlock = new InputFilesBlock();
            $template->setFictive();
            $template->scenario = InputFile::SCENARIO_CREATE;

            return $template;
        });
    }

    /**
     * Return instance of input file for real file
     * @param $name
     * @return bool|object
     */
    private function forRealFileBlock($name)
    {
        return $this->getOrSet($name, function() use($name) {
            $inputFileBlock = InputFilesBlock::getInstance(
                $this->aggregator->getInputFileTemplateReference(),
                $name,
                $this->aggregator->getInputFileReference()
            );

            $inputFileBlock->scenario = InputFile::SCENARIO_UPDATE;

            return $inputFileBlock;
        });
    }
}
