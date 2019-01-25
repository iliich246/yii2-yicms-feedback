<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsFeedback\Base\Feedback;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */

$js = <<<JS
;(function() {
    var pjaxContainer   = $('#update-feedback-container');
    var pjaxContainerId = '#update-feedback-container';


    $(pjaxContainer).on('pjax:success', function() {
        $(".alert").hide().slideDown(500).fadeTo(500, 1);

        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove();
            });
        }, 3000);
    });

    $(pjaxContainer).on('pjax:error', function(xhr, textStatus) {
        bootbox.alert({
            size: 'large',
            title: "There are some error on ajax request!",
            message: textStatus.responseText,
            className: 'bootbox-error'
        });
    });

    $('#feedback-delete').on('click',  function() {
        var button = this;

        if (!$(button).is('[data-feedback-id]')) return;

        var feedbackId             = $(button).data('feedbackId');
        var feedbackHasConstraints = $(button).data('feedbackHasConstraints');
        var homeUrl                = $(button).data('homeUrl');
        var deleteUrl              = homeUrl + '/feedback/dev/delete-feedback';

        if (!($(this).hasClass('feedback-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('feedback-confirm-state');
        } else {
            if (!feedbackHasConstraints) {
                $.pjax({
                    url: deleteUrl + '?id=' + feedbackId,
                    container: pjaxContainerId,
                    scrollTo: false,
                    push: false,
                    type: "POST",
                    timeout: 2500
                 });
            } else {
                var deleteButtonRow = $('.delete-button-row');

                var template = _.template($('#delete-with-pass-template').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);

                var passwordInput = $('#feedback-delete-password-input');
                var buttonDelete  = $('#button-delete-with-pass');

                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: deleteUrl + '?id=' + feedbackId +
                                         '&deletePass=' + $(passwordInput).val(),
                        container: pjaxContainerId,
                        scrollTo: false,
                        push: false,
                        type: "POST",
                        timeout: 2500
                    });
                });

                $(pjaxContainer).on('pjax:error', function(event) {
                    bootbox.alert({
                        size: 'large',
                        title: "Wrong dev password",
                        message: "Page has not deleted",
                        className: 'bootbox-error'
                    });
                });
            }
        }
    });
})();
JS;

$this->registerJs($js, $this::POS_READY);


?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <?php if ($feedback->scenario == Feedback::SCENARIO_CREATE): ?>
            <h1>Create feedback</h1>
        <?php else: ?>
            <h1>Update feedback</h1>
            <h2>IMPORTANT! Do not change feedback names in production without serious reason!</h2>
        <?php endif; ?>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>"><span>Feedback list</span></a> <span> / </span>
        <?php if ($feedback->scenario == Feedback::SCENARIO_CREATE): ?>
            <span>Create feedback</span>
        <?php else: ?>
            <span>Update feedback</span>
        <?php endif; ?>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">
                <?php if ($feedback->scenario == Feedback::SCENARIO_CREATE): ?>
                    <h3>Create feedback</h3>
                <?php else: ?>
                    <h3>Update feedback</h3>
                <?php endif; ?>
            </div>
            <?php if ($feedback->scenario == Feedback::SCENARIO_UPDATE): ?>
                <div class="row control-buttons">
                    <div class="col-xs-12">

                        <a href="<?= Url::toRoute(['feedback-translates', 'id' => $feedback->id]) ?>"
                           class="btn btn-primary">
                            Feedback name translates
                        </a>

                        <a href="<?= Url::toRoute([
                            '/feedback/dev/feedback-page-templates', 'id' => $feedback->id
                        ]) ?>"
                           class="btn btn-primary">
                            Feedback page templates
                        </a>

                        <a href="<?= Url::toRoute([
                            '/feedback/dev/feedback-input-templates', 'id' => $feedback->id
                        ]) ?>"
                           class="btn btn-primary">
                            Feedback input templates
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-feedback-container',
                ],
                'enablePushState'    => false,
                'enableReplaceState' => false
            ]) ?>
            <?php $form = ActiveForm::begin([
                'id' => 'create-update-feedback-form',
                'options' => [
                    'data-pjax' => true,
                ],
            ]);
            ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">?</span></button>
                    <strong>Success!</strong> Feedback data updated.
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($feedback, 'program_name') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($feedback, 'editable')->checkbox() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($feedback, 'visible')->checkbox() ?>
                </div>
            </div>


            <?php if ($feedback->scenario == Feedback::SCENARIO_UPDATE): ?>
                <div class="row delete-button-row">
                    <div class="col-xs-12">
                        <br>
                        <button type="button"
                                class="btn btn-danger"
                                data-home-url="<?= \yii\helpers\Url::base() ?>"
                                data-feedback-id="<?= $feedback->id ?>"
                                data-feedback-has-constraints="<?= (int)$feedback->isConstraints() ?>"
                                id="feedback-delete">
                            Delete feedback
                        </button>
                    </div>
                </div>
                <script type="text/template" id="delete-with-pass-template">
                    <div class="col-xs-12">
                        <br>
                        <label for="feedback-delete-password-input">
                            Feedback has constraints. Enter dev password for delete feedback
                        </label>
                        <input type="password"
                               id="feedback-delete-password-input"
                               class="form-control" name=""
                               value=""
                               aria-required="true"
                               aria-invalid="false">
                        <br>
                        <button type="button"
                                class="btn btn-danger"
                                id="button-delete-with-pass"
                            >
                            Yes, i am absolutely seriously!!!
                        </button>
                    </div>
                </script>
            <?php endif; ?>
            <div class="row control-buttons">
                <div class="col-xs-12">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                    <?= Html::resetButton('Cancel', ['class' => 'btn btn-default cancel-button']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
