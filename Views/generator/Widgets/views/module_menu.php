<?php //template

use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback[] */
/** @var $widget \app\yicms\Feedback\Widgets\ModuleMenuWidget */

?>

<?php foreach ($feedback as $feedbackOne): ?>
    <div class="row link-block">
        <div class="col-xs-12">
            <h2><?= $feedbackOne->name() ?></h2>
            <a <?php if (($widget->route == 'feedback/admin/elements-list')
                &&
                Yii::$app->request->get('feedbackId') == $feedbackOne->id): ?> class="active"
            <?php endif; ?>
                href="<?= Url::toRoute(['/feedback/admin/elements-list', 'feedbackId' => $feedbackOne->id]) ?>">

                Elements list
                <?php if ($feedbackOne->isNewStates()): ?>
                    <span class="badge"><?= $feedbackOne->countNewStates() ?></span>
                <?php endif; ?>
            </a>
            <?php if ($feedbackOne->admin_can_edit_fields ||
                     (!$feedbackOne->admin_can_edit_fields && \Iliich246\YicmsCommon\CommonModule::isUnderDev())) ?>
            <a href="<?= Url::toRoute(['/feedback/admin/feedback-pages', 'feedbackId' => $feedbackOne->id]) ?>">
                Feedback pages
            </a>
        </div>
    </div>
    <hr>
<?php endforeach; ?>
