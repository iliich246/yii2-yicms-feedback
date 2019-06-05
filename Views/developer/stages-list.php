<?php

use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $feedbackStages \Iliich246\YicmsFeedback\Base\FeedbackStages[] */

$js = <<<JS
;(function() {
        var pjaxContainer = $('#update-feedback-stage-list-container');

    $(document).on('click', '.glyphicon-arrow-up', function() {
        $.pjax({
            url: $(this).data('url'),
            container: '#update-feedback-stage-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.glyphicon-arrow-down', function() {
        $.pjax({
            url: $(this).data('url'),
            container: '#update-feedback-stage-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(pjaxContainer).on('pjax:error', function(xhr, textStatus) {
        bootbox.alert({
            size: 'large',
            title: "There are some error on ajax request!",
            message: textStatus.responseText,
            className: 'bootbox-error'
        });
    });
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
            <?= $this->render('@yicms-feedback/Views/pjax/update-feedback-stages-list-container', [
                'feedbackStages' => $feedbackStages
            ]) ?>
        </div>
    </div>
</div>
