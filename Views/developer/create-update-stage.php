<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsFeedback\Base\FeedbackStages;

/** @var $this \yii\web\View */
/** @var $feedbackStage \Iliich246\YicmsFeedback\Base\FeedbackStages */

$js = <<<JS
;(function() {
    var pjaxContainer   = $('#update-feedback-stage-container');
    var pjaxContainerId = '#update-feedback-stage-container';


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

    $('#feedback-stage-delete').on('click',  function() {
        var button = this;

        if (!$(button).is('[data-feedback-stage-id]')) return;

        var feedbackStageId             = $(button).data('feedbackStageId');
        var feedbackStageHasConstraints = $(button).data('feedbackStageHasConstraints');
        var homeUrl                = $(button).data('homeUrl');
        var deleteUrl              = homeUrl + '/feedback/dev/delete-stage';

        if (!($(this).hasClass('feedback-stage-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('feedback-stage-confirm-state');
        } else {
            if (!feedbackStageHasConstraints) {
                $.pjax({
                    url: deleteUrl + '?id=' + feedbackStageId,
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

                var passwordInput = $('#feedback-stage-delete-password-input');
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
        <?php if ($feedbackStage->scenario == FeedbackStages::SCENARIO_CREATE): ?>
            <h1>Create feedback stage</h1>
        <?php else: ?>
            <h1>Update feedback stage</h1>
            <h2>IMPORTANT! Do not change feedback stage names in production without serious reason!</h2>
        <?php endif; ?>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>">
            <span>Feedback list</span>
        </a>

        <span> / </span>

        <a href="<?= Url::toRoute(['update-feedback', 'id' => $feedbackStage->getFeedback()->id]) ?>">
            <span>Feedback update</span>
        </a>

        <span> / </span>

        <a href="<?= Url::toRoute(['stages-list', 'id' => $feedbackStage->getFeedback()->id]) ?>">
            <span>Stages list</span>
        </a>

        <span> / </span>

        <?php if ($feedbackStage->scenario == FeedbackStages::SCENARIO_CREATE): ?>
            <span>Create feedback stage</span>
        <?php else: ?>
            <span>Update feedback stage</span>
        <?php endif; ?>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">
            <div class="content-block-title">
                <?php if ($feedbackStage->scenario == FeedbackStages::SCENARIO_CREATE): ?>
                    <h3>Create feedback stage</h3>
                <?php else: ?>
                    <h3>Update feedback stage</h3>
                <?php endif; ?>
            </div>
            <?php if ($feedbackStage->scenario == FeedbackStages::SCENARIO_UPDATE): ?>
                <div class="row control-buttons">
                    <div class="col-xs-12">
                        <a href="<?= Url::toRoute(['stage-translates', 'id' => $feedbackStage->id]) ?>"
                           class="btn btn-primary">
                            Feedback name translates
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-feedback-stage-container',
                ]
            ]) ?>
            <?php $form = ActiveForm::begin([
                'id' => 'create-update-feedback-stage-form',
                'options' => [
                    'data-pjax' => true,
                ],
            ]);
            ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">?</span></button>
                    <strong>Success!</strong> Feedback stage data updated.
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($feedbackStage, 'program_name') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($feedbackStage, 'editable')->checkbox() ?>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?= $form->field($feedbackStage, 'visible')->checkbox() ?>
                </div>
            </div>


            <?php if ($feedbackStage->scenario == FeedbackStages::SCENARIO_UPDATE): ?>
                <div class="row delete-button-row">
                    <div class="col-xs-12">
                        <br>
                        <button type="button"
                                class="btn btn-danger"
                                data-home-url="<?= \yii\helpers\Url::base() ?>"
                                data-feedback-stage-id="<?= $feedbackStage->id ?>"
                                data-feedback-stage-has-constraints="<?= (int)$feedbackStage->isConstraints() ?>"
                                id="feedback-delete">
                            Delete feedback stage
                        </button>
                    </div>
                </div>
                <script type="text/template" id="delete-with-pass-template">
                    <div class="col-xs-12">
                        <br>
                        <label for="feedback-delete-password-input">
                            Feedback stage has constraints. Enter dev password for delete feedback stage
                        </label>
                        <input type="password"
                               id="feedback-stage-delete-password-input"
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
