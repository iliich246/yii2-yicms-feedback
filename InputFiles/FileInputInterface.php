<?php

namespace Iliich246\YicmsFeedback\InputFiles;

/**
 * Interface FileInputInterface
 *
 * This interface must implement any class, that must has input files functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface FileInputInterface
{
    /**
     * @return FilesInputHandler object, that aggregated in object with files functionality.
     */
    public function getInputFileHandler();

    /**
     * This method must proxy FilesHandler method for work with him directly from aggregator.
     * @param $name
     * @return InputFile
     */
    public function getInputFile($name);
}
