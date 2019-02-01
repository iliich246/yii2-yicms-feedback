<?php

use yii\widgets\Pjax;
use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback[] */

?>

<?php Pjax::begin([
    'options' => [
        'id' => 'update-feedback-list-container'
    ],
    'linkSelector' => false,
]) ?>
<div class="list-block">
    <?php foreach($feedback as $oneFeedback): ?>
        <div class="row list-items">
            <div class="col-xs-10 list-title">
                <a href="<?= Url::toRoute(['update-feedback', 'id' => $oneFeedback->id]) ?>">
                    <p>
                        <?= $oneFeedback->program_name ?>
                    </p>
                </a>
            </div>
            <div class="col-xs-2 list-controls">
                <?php if ($oneFeedback->active): ?>
                    <span class="glyphicon glyphicon-eye-open"></span>
                <?php else: ?>
                    <span class="glyphicon glyphicon-eye-close"></span>
                <?php endif; ?>
                <?php if ($oneFeedback->editable): ?>
                    <span class="glyphicon glyphicon-pencil"></span>
                <?php endif; ?>
                <?php if ($oneFeedback->canUpOrder()): ?>
                    <span class="glyphicon glyphicon-arrow-up"
                          data-url="<?= Url::toRoute(['/feedback/dev/feedback-up-order', 'id' => $oneFeedback->id]) ?>"></span>
                <?php endif; ?>
                <?php if ($oneFeedback->canDownOrder()): ?>
                    <span class="glyphicon glyphicon-arrow-down"
                          data-url="<?= Url::toRoute(['/feedback/dev/feedback-down-order', 'id' => $oneFeedback->id]) ?>"></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php Pjax::end() ?>
