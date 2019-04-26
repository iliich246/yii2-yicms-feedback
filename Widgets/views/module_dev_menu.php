<?php

use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $widget \Iliich246\YicmsEssences\Widgets\ModuleDevMenuWidget */

?>

<div class="row link-block">
    <div class="col-xs-12">
        <h2>Feedback module</h2>
        <a <?php if ($widget->route == 'feedback/dev/list'): ?> class="active" <?php endif; ?>
            href="<?= Url::toRoute('/feedback/dev/list') ?>">
            List of feedback
        </a>
        <a <?php if (
            ($widget->route == 'feedback/dev/create-feedback')
            ||
            ($widget->route == 'feedback/dev/update-feedback')
        ):?> class="active" <?php endif; ?>
            href="<?= Url::toRoute('/feedback/dev/create-feedback') ?>">
            Create/update feedback
        </a>
    </div>
</div>
<hr>

