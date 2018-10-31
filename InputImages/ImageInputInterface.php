<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Iliich246\YicmsCommon\Images\ImagesBlock;
use Iliich246\YicmsCommon\Images\ImagesHandler;

/**
 * Interface ImageInputInterface
 *
 * This interface must implement any class, that must has input images functionality.
 *
 * @author iliich246 <iliich246@gmail.com>
 */
interface ImageInputInterface
{
    /**
     * @return ImagesHandler object, that aggregated in object with input images functionality.
     */
    public function getInputImagesHandler();

    /**
     * This method must proxy ImagesHandler method for work with him directly from aggregator.
     * @param $name
     * @return ImagesBlock
     */
    public function getInputImageBlock($name);
}
