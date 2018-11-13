<?php

namespace Iliich246\YicmsFeedback\InputImages;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Widget;

/**
 * Class InputImagesDevModalWidget
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputImagesDevModalWidget extends Widget
{
    /** @var DevInputImagesGroup */
    public $devInputImagesGroup;
    /** @var bool true means that widget initialized after success data save in DevInputImagesGroup */
    public $dataSaved = false;
    /** @var string part of link for delete input image block template */
    public $deleteLink;
    /** @var string keeps current form action */
    public $action;
    /** @var string if true widget must close modal window after save data */
    public $saveAndExit = 'false';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->deleteLink = Url::toRoute(['/feedback/dev-input-images/delete-input-image-block']);

        if (Yii::$app->request->post('_saveAndExit'))
            $this->saveAndExit = 'true';
    }

    /**
     * Returns name of form name of widget
     * @return string
     */
    public static function getFormName()
    {
        return 'create-update-input-images';
    }

    /**
     * Return name of modal window of widget
     * @return string
     */
    public static function getModalWindowName()
    {
        return 'inputImagesDevModal';
    }

    /**
     * Returns name of pjax container for this widget
     * @return string
     */
    public static function getPjaxContainerId()
    {
        return 'input-images-pjax-container';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('input_images_dev_modal_widget', [
            'widget' => $this
        ]);
    }
}
