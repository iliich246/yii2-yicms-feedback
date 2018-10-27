<?php

use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $feedbackStages \Iliich246\YicmsFeedback\Base\FeedbackStages[] */

$js = <<<JS
;(function() {
    
})();
JS;

$this->registerJs($js, $this::POS_READY);

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>List of feedback stages</h1>
    </div>
    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>"><span>Feedback list</span></a> <span> / </span>
        <a href="<?= Url::toRoute(['update-feedback', 'id' => $feedback->id]) ?>"><span>Feedback update</span></a> <span> / </span>

        <span>Stages list</span>

    </div>
    <div class="row content-block">
        <div class="col-xs-12">
            <div class="row control-buttons">
                <div class="col-xs-12">
                    <a href="<?= Url::toRoute(['create-stage', 'id' => $feedback->id]) ?>"
                       class="btn btn-primary create-feedback-button"
                       data-home-url="<?= Url::base() ?>">
                        Create new feedback stage
                    </a>
                </div>
            </div>
            <?= $this->render('/pjax/update-feedback-stages-list-container', [
                'feedbackStages' => $feedbackStages
            ]) ?>
        </div>
    </div>
</div>
