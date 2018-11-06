<?php

namespace Iliich246\YicmsFeedback\InputFields;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Widget;

/**
 * Class InputFieldsDevModalWidget
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputFieldsDevModalWidget extends Widget
{
    /** @var DevInputFieldsGroup instance  */
    public $devInputFieldGroup;
    /** @var bool true means that widget initialized after success data save in devFieldGroup */
    public $dataSaved = false;
    /** @var string part of link for delete field template  */
    public $deleteLink;
    /** @var string keeps current form action  */
    public $action;
    /** @var string if true widget must close modal window after save data */
    public $saveAndExit = 'false';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->deleteLink = Url::toRoute(['/feedback/dev-fields/delete-field-template']);

        if (Yii::$app->request->post('_saveAndExit'))
            $this->saveAndExit = 'true';
    }

    /**
     * Returns name of form name of widget
     * @return string
     */
    public static function getFormName()
    {
        return 'create-update-input-fields';
    }

    /**
     * Return name of modal window of widget
     * @return string
     */
    public static function getModalWindowName()
    {
        return 'inputFieldsDevModal';
    }

    /**
     * Returns name of pjax container for this widget
     * @return string
     */
    public static function getPjaxContainerId()
    {
        return 'input-fields-pjax-container';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('input_fields_dev_modal_widget', [
            'widget' => $this
        ]);
    }
}
