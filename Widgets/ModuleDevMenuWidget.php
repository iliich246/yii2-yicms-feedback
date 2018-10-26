<?php

namespace Iliich246\YicmsFeedback\Widgets;

use Yii;
use Iliich246\YicmsCommon\Base\AbstractModuleMenuWidget;

/**
 * Class ModuleDevMenuWidget
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class ModuleDevMenuWidget extends AbstractModuleMenuWidget
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('module_dev_menu', [
            'widget' => $this,
        ]);
    }
}
