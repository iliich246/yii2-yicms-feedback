<?php

namespace app\yicms\Feedback\Widgets;

use Iliich246\YicmsFeedback\Base\Feedback;
use Yii;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsCommon\Base\AbstractModuleMenuWidget;

/**
 * Class ModuleMenuWidget
 */
class ModuleMenuWidget extends AbstractModuleMenuWidget
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->route = Yii::$app->controller->action->getUniqueId();

        $feedbackQuery = Feedback::find()->orderBy([
            'feedback_order' => SORT_ASC
        ]);

        if (!CommonModule::isUnderDev())
            $feedbackQuery->where([
                'editable' => true,
            ]);

        $feedback = $feedbackQuery->all();

        return $this->render('module_menu', [
            'widget'   => $this,
            'feedback' => $feedback
        ]);
    }
}
