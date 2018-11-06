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
    /**
     * @inheritdoc
     */
    public function init()
    {

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
