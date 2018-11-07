<?php

namespace Iliich246\YicmsFeedback;

use Yii;
use Iliich246\YicmsCommon\Base\YicmsModuleInterface;
use Iliich246\YicmsCommon\Base\AbstractConfigurableModule;

/**
 * Class FeedbackModule
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackModule extends AbstractConfigurableModule implements YicmsModuleInterface
{
    /** @inheritdoc */
    public $controllerMap = [
        'dev'                   => 'Iliich246\YicmsFeedback\Controllers\DeveloperController',
        'dev-input-fields'      => 'Iliich246\YicmsFeedback\Controllers\DeveloperInputFieldsController',
        'dev-input-validators'  => 'Iliich246\YicmsFeedback\Controllers\DeveloperValidatorsController',
        'dev-input-files'       => 'Iliich246\YicmsFeedback\Controllers\DeveloperFilesController',
        'dev-input-images'      => 'Iliich246\YicmsFeedback\Controllers\DeveloperImagesController',
        'dev-input-conditions'  => 'Iliich246\YicmsFeedback\Controllers\DeveloperConditionsController',
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
