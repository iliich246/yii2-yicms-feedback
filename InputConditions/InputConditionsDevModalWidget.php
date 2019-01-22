<?php

namespace Iliich246\YicmsFeedback\InputConditions;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Widget;

/**
 * Class InputConditionsDevModalWidget
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class InputConditionsDevModalWidget extends Widget
{
    /** @var DevInputConditionsGroup */
    public $devInputConditionsGroup;
    /** @var bool true means that widget initialized after success data save in DevInputConditionsGroup */
    public $dataSaved = false;
    /** @var string part of link for delete input condition template */
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
        $this->deleteLink = Url::toRoute(['/feedback/dev-input-conditions/delete-input-condition-template']);

        if (Yii::$app->request->post('_saveAndExit'))
            $this->saveAndExit = 'true';
    }

    /**
     * Returns name of form name of widget
     * @return string
     */
    public static function getFormName()
    {
        return 'create-update-input-conditions';
    }

    /**
     * Return name of modal window of widget
     * @return string
     */
    public static function getModalWindowName()
    {
        return 'inputConditionsDevModal';
    }

    /**
     * Returns name of pjax container for this widget
     * @return string
     */
    public static function getPjaxContainerId()
    {
        return 'input-conditions-pjax-container';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('input_conditions_dev_modal_widget', [
            'widget' => $this
        ]);
    }
}
