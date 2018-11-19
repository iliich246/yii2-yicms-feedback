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
    public $devInputFilesGroup;
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
        $this->deleteLink = Url::toRoute(['/feedback/dev-input-conditions/delete-input-file-block']);

        if (Yii::$app->request->post('_saveAndExit'))
            $this->saveAndExit = 'true';
    }
}
