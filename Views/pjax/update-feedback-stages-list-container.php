<?php

use yii\widgets\Pjax;
use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $feedbackStages \Iliich246\YicmsFeedback\Base\FeedbackStages[] */

?>

<?php Pjax::begin([
    'options' => [
        'id' => 'update-feedback-stage-list-container'
    ],
    'linkSelector' => false,
]) ?>
<div class="list-block">
    <?php foreach($feedbackStages as $stage): ?>
        <div class="row list-items">
            <div class="col-xs-10 list-title">
                <a href="<?= Url::toRoute(['update-stage', 'id' => $stage->id]) ?>">
                    <p>
                        <?= $stage->program_name ?>
                    </p>
                </a>
            </div>
            <div class="col-xs-2 list-controls">
                <?php if ($stage->visible): ?>
                    <span class="glyphicon glyphicon-eye-open"></span>
                <?php else: ?>
                    <span class="glyphicon glyphicon-eye-close"></span>
                <?php endif; ?>
                <?php if ($stage->editable): ?>
                    <span class="glyphicon glyphicon-pencil"></span>
                <?php endif; ?>
                <?php if ($stage->canUpOrder()): ?>
                    <span class="glyphicon glyphicon-arrow-up"
                          data-url="<?= Url::toRoute([
                              '/feedback/dev/stage-up-order', 'id' => $stage->id
                          ]) ?>">
                    </span>
                <?php endif; ?>
                <?php if ($stage->canDownOrder()): ?>
                    <span class="glyphicon glyphicon-arrow-down"
                          data-url="<?= Url::toRoute([
                              '/feedback/dev/stage-down-order', 'id' => $stage->id
                          ]) ?>">
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php Pjax::end() ?>
