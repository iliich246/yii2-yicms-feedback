<?php

use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback[] */

$js = <<<JS
;(function() {
    var pjaxContainer = $('#update-feedback-list-container');

    $(document).on('click', '.glyphicon-arrow-up', function() {
        $.pjax({
            url: $(this).data('url'),
            container: '#update-feedback-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.glyphicon-arrow-down', function() {
        $.pjax({
            url: $(this).data('url'),
            container: '#update-feedback-list-container',
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
        <h1>List of feedback sets</h1>
    </div>
    <div class="row content-block">
        <div class="col-xs-12">
            <div class="row control-buttons">
                <div class="col-xs-12">
                    <a href="<?= Url::toRoute(['create-feedback']) ?>"
                       class="btn btn-primary create-feedback-button"
                       data-url="<?= Url::base() ?>">
                        Create new feedback
                    </a>
                </div>
            </div>
            <?= $this->render('/pjax/update-feedback-list-container', [
                'feedback' => $feedback
            ]) ?>
        </div>
    </div>
</div>
