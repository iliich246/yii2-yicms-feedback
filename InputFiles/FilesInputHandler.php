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
            $nonexistentInputFile = new InputFile();
            $nonexistentInputFile->setNonexistent();
            $nonexistentInputFile->setNonexistentName($name);

            return $nonexistentInputFile;
        }

        if (!$this->aggregator->isFictive()) return $this->forRealFile($name);

        return $this->forFictiveFile($name);
    }

    /**
     * Makes fictive input file
     * @param $name
     * @return bool|object
     */
    private function forFictiveFile($name)
    {
        return $this->getOrSet($name, function() use($name) {
            /** @var InputFilesBlock $template */
            $template =  InputFilesBlock::getInstance($this->aggregator->getInputFileTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputFile = new InputFile();
                $nonexistentInputFile->setNonexistent();
                $nonexistentInputFile->setNonexistentName($name);

                return $nonexistentInputFile;
            }

            $fictiveFile = new InputFile();
            $fictiveFile->setFictive();
            $fictiveFile->setEntityBlock($template);

            return $fictiveFile;
        });
    }

    /**
     * Return instance of input file for real file
     * @param $name
     * @return bool|object
     */
    private function forRealFile($name)
    {
        return $this->getOrSet($name, function() use($name) {
            return InputFilesBlock::getInstance(
                $this->aggregator->getInputFileTemplateReference(),
                $name,
                $this->aggregator->getInputFileReference()
            );
        });
    }
}
