<?php

namespace Iliich246\YicmsFeedback;

use Iliich246\YicmsFeedback\Base\FeedbackException;
use Yii;
use yii\base\BootstrapInterface;
use Iliich246\YicmsCommon\Base\YicmsModuleInterface;
use Iliich246\YicmsCommon\Base\AbstractConfigurableModule;

/**
 * Class FeedbackModule
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackModule extends AbstractConfigurableModule implements
    BootstrapInterface,
    YicmsModuleInterface
{


    /**
     * Block of fields with various paths
     */
    public $filesPatch           = DIRECTORY_SEPARATOR .
                                'web' . DIRECTORY_SEPARATOR .
                                'input_files' . DIRECTORY_SEPARATOR;

    public $imagesOriginalsPath  = DIRECTORY_SEPARATOR .
                                'web' . DIRECTORY_SEPARATOR .
                                'input_images' . DIRECTORY_SEPARATOR;

    /**
     * Block of variables with images web paths
     */
    public $imagesOriginalsWebPath  = 'input_images/orig/';

    /** @inheritdoc */
    public $controllerMap = [
        'dev'                   => 'Iliich246\YicmsFeedback\Controllers\DeveloperController',
        'dev-input-fields'      => 'Iliich246\YicmsFeedback\Controllers\DeveloperInputFieldsController',
        'dev-input-files'       => 'Iliich246\YicmsFeedback\Controllers\DeveloperInputFilesController',
        'dev-input-images'      => 'Iliich246\YicmsFeedback\Controllers\DeveloperInputImagesController',
        'dev-input-conditions'  => 'Iliich246\YicmsFeedback\Controllers\DeveloperInputConditionsController',
    ];


    /**
     * @inheritdoc
     */
    public function init()
    {
        //TODO: makes correct build of controller map via common->$yicmsLocation
        $this->controllerMap['admin'] = 'app\yicms\Feedback\Controllers\AdminController';
        parent::init();
    }


    /**
     * @inheritdoc
     * @throws FeedbackException
     */
    public function bootstrap($app)
    {

    }

    /**
     * Proxy translate method from module to framework
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     * @return mixed
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        //Implement this method correctly
        return $message;
    }

    /**
     * @inherited
     */
    public function getNameSpace()
    {
        return __NAMESPACE__;
    }

    /**
     * @inherited
     */
    public static function getModuleName()
    {
        return 'Feedback';
    }
}
