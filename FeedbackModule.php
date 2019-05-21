<?php

namespace Iliich246\YicmsFeedback;

use Yii;
use yii\base\BootstrapInterface;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\Generator;
use Iliich246\YicmsCommon\Base\YicmsModuleInterface;
use Iliich246\YicmsCommon\Base\AbstractConfigurableModule;
use Iliich246\YicmsFeedback\Base\FeedbackConfigDb;

/**
 * Class FeedbackModule
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class FeedbackModule extends AbstractConfigurableModule implements
    BootstrapInterface,
    YicmsModuleInterface
{
    /** @var bool keeps true if for this module was generated changeable admin files */
    public $isGenerated = false;
    /** @var bool if true generator will be generate in strong mode, even existed files will be replaced */
    public $strongGenerating = false;

    /** @inheritdoc */
    public $configurable = [
        'isGenerated',
    ];

    /**
     * Block of fields with various paths
     */
    public $inputFilesPatch = DIRECTORY_SEPARATOR .
                            'web' . DIRECTORY_SEPARATOR .
                            'input_files' . DIRECTORY_SEPARATOR;

    public $inputImagesPath = DIRECTORY_SEPARATOR .
                             'web' . DIRECTORY_SEPARATOR .
                             'input_images' . DIRECTORY_SEPARATOR;

    /**
     * Block of variables with images web paths
     */
    public $inputImagesWebPath  = 'input_images/';

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
        Yii::setAlias('@yicms-feedback', Yii::getAlias('@vendor') .
            DIRECTORY_SEPARATOR .
            'iliich246' .
            DIRECTORY_SEPARATOR .
            'yii2-yicms-feedback');

        parent::init();

        $namespace = CommonModule::getInstance()->yicmsNamespace . '\Feedback\Controllers\\';

        $this->controllerMap['admin'] = $namespace . 'AdminController';

        $this->inputFilesPatch = Yii::$app->basePath . $this->inputFilesPatch;
        $this->inputImagesPath = Yii::$app->basePath . $this->inputImagesPath;

        $this->inputImagesWebPath = Yii::$app->homeUrl . $this->inputImagesWebPath;
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $generator = new Generator($this);
        $generator->generate();
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
    public function getModuleDir()
    {
        return __DIR__;
    }

    /**
     * @inherited
     */
    public function isGenerated()
    {
        return !!$this->isGenerated;
    }

    /**
     * @inherited
     */
    public function setAsGenerated()
    {
        $config = FeedbackConfigDb::getInstance();
        $config->isGenerated = true;

        $config->save(false);
    }

    /**
     * @inherited
     */
    public function isGeneratorInStrongMode()
    {
        return !!$this->strongGenerating;
    }

    /**
     * @inherited
     */
    public static function getModuleName()
    {
        return 'Feedback';
    }
}
