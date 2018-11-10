<?php

namespace Iliich246\YicmsFeedback\InputFiles;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Widget;

/**
 * Class InputFilesDevModalWidget
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFilesDevModalWidget extends Widget
{
    /** @var InputDevFilesGroup */
    public $devInputFilesGroup;
    /** @var bool true means that widget initialized after success data save in DevFilesGroup */
    public $dataSaved = false;
    /** @var string part of link for delete file block template */
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
        $this->deleteLink = Url::toRoute(['/feedback/dev-input-files/delete-input-file-block-template']);

        if (Yii::$app->request->post('_saveAndExit'))
            $this->saveAndExit = 'true';
    }

    /**
     * Returns name of form name of widget
     * @return string
     */
    public static function getFormName()
    {
        return 'create-update-input-files';
    }

    /**
     * Return name of modal window of widget
     * @return string
     */
    public static function getModalWindowName()
    {
        return 'inputFilesDevModal';
    }

    /**
     * Returns name of pjax container for this widget
     * @return string
     */
    public static function getPjaxContainerId()
    {
        return 'input-files-pjax-container';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('input_files_dev_modal_widget', [
            'widget' => $this
        ]);
    }
}
