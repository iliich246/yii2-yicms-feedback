<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use Iliich246\YicmsCommon\Base\AbstractHandler;
use Iliich246\YicmsCommon\Base\FictiveInterface;
use Iliich246\YicmsCommon\Base\NonexistentInterface;

/**
 * Class FilesInputHandler
 *
 * Object of this class must aggregate any object, that must implement input files functionality.
 *
 * @property FileInputReferenceInterface|NonexistentInterface|FictiveInterface|AnnotatorFileInterface $aggregator
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
     * Returns true if aggregator has input file block with name
     * @param $name
     * @return bool
     */
    public function isInputFileBlock($name)
    {
        if ($this->aggregator->isNonexistent()) return false;

        if (!$this->aggregator->isAnnotationActive())
            return InputFilesBlock::isTemplate($this->aggregator->getInputFileTemplateReference(), $name);

        /** @var InputFilesBlock $className */
        $className = $this->getClassName($name);

        if (class_exists($className))
            return $className::isTemplate($this->aggregator->getInputFileTemplateReference(), $name);

        return InputFilesBlock::isTemplate($this->aggregator->getInputFileTemplateReference(), $name);
    }

    /**
     * Makes fictive input file
     * @param $name
     * @return bool|object
     */
    private function forFictiveFileBlock($name)
    {
        return $this->getOrSet($name, function() use($name) {
            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    /** @var InputFilesBlock $template */
                    $template = InputFilesBlock::getInstance($this->aggregator->getInputFileTemplateReference(), $name);

                    if (!$template) {
                        $nonexistentInputFileBlock = new InputFilesBlock();
                        $nonexistentInputFileBlock->setNonexistent();
                        $nonexistentInputFileBlock->setNonexistentName($name);

                        return $nonexistentInputFileBlock;
                    }

                    $template->setFictive();
                    $template->scenario = InputFile::SCENARIO_CREATE;

                    return $template;
                }

                /** @var InputFilesBlock $className */
                $className = $this->getClassName($name);

                if (class_exists($className)) {
                    /** @var InputFilesBlock $template */
                    $template = $className::getInstance($this->aggregator->getInputFileTemplateReference(), $name);

                    if (!$template) {
                        $nonexistentInputFileBlock = new InputFilesBlock();
                        $nonexistentInputFileBlock->setNonexistent();
                        $nonexistentInputFileBlock->setNonexistentName($name);

                        return $nonexistentInputFileBlock;
                    }

                    $template->setFictive();
                    $template->scenario = InputFile::SCENARIO_CREATE;
                    $template->setParentFileAnnotator($this->aggregator);

                    return $template;
                }
            }

            /** @var InputFilesBlock $template */
            $template = InputFilesBlock::getInstance($this->aggregator->getInputFileTemplateReference(), $name);

            if (!$template) {
                $nonexistentInputFileBlock = new InputFilesBlock();
                $nonexistentInputFileBlock->setNonexistent();
                $nonexistentInputFileBlock->setNonexistentName($name);

                return $nonexistentInputFileBlock;
            }

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
            if ($this->aggregator instanceof AnnotatorFileInterface) {
                if (!$this->aggregator->isAnnotationActive()) {
                    $inputFileBlock = InputFilesBlock::getInstance(
                        $this->aggregator->getInputFileTemplateReference(),
                        $name,
                        $this->aggregator->getInputFileReference()
                    );

                    $inputFileBlock->scenario = InputFile::SCENARIO_UPDATE;

                    return $inputFileBlock;
                }

                /** @var InputFilesBlock $className */
                $className = $this->getClassName($name);

                if (class_exists($className)) {
                    $inputFileBlock = $className::getInstance(
                        $this->aggregator->getInputFileTemplateReference(),
                        $name,
                        $this->aggregator->getInputFileReference()
                    );
                } else {
                    $inputFileBlock = InputFilesBlock::getInstance(
                        $this->aggregator->getInputFileTemplateReference(),
                        $name,
                        $this->aggregator->getInputFileReference()
                    );
                }

                $inputFileBlock->scenario = InputFile::SCENARIO_UPDATE;
                $inputFileBlock->setParentFileAnnotator($this->aggregator);

                return $inputFileBlock;
            }

            $inputFileBlock = InputFilesBlock::getInstance(
                $this->aggregator->getInputFileTemplateReference(),
                $name,
                $this->aggregator->getInputFileReference()
            );

            $inputFileBlock->scenario = InputFile::SCENARIO_UPDATE;

            return $inputFileBlock;
        });
    }

    /**
     * Return class name for annotated files block
     * @param $name
     * @return string
     */
    private function getClassName($name)
    {
        return $this->aggregator->getAnnotationFileNamespace() . '\\' .
        $this->aggregator->getAnnotationFileName() . '\\InputFiles\\' .
        ucfirst(mb_strtolower($name)) . 'InputFilesBlock';
    }
}
