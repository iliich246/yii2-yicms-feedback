<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;

/** @var $this \yii\web\View */
/** @var $feedbackStage \Iliich246\YicmsFeedback\Base\FeedbackStages */
/** @var $translateModels \Iliich246\YicmsFeedback\Base\FeedbackStagesDevTranslateForm[] */


$this->title = "Translations of feedback stages names";

$js = <<<JS
;(function() {
    var pjaxContainer   = $('#edit-feedback-stages-names-container');
    var pjaxContainerId = '#edit-feedback-stages-names-container';

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
})();
JS;

$this->registerJs($js, $this::POS_READY);

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>Translations of feedback stages names</h1>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>">
            <span>Feedback list</span>
        </a>

        <span> / </span>

        <a href="<?= Url::toRoute(['update-feedback', 'id' => $feedbackStage->getFeedback()->id]) ?>">
            <span>Feedback update (<?= $feedbackStage->getFeedback()->program_name ?>)</span>
        </a>

        <span> / </span>

        <a href="<?= Url::toRoute(['stages-list', 'id' => $feedbackStage->getFeedback()->id]) ?>">
            <span>Feedback stages list</span>
        </a>

        <span> / </span>

        <a href="<?= Url::toRoute(['update-stage', 'id' => $feedbackStage->id]) ?>">
            <span>Update stage (<?= $feedbackStage->program_name ?>)</span>
        </a>

        <span> / </span>

        <span>Translations of feedback stages names</span>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">

            <div class="content-block-title">
                <h3>Feedback stages names form</h3>
                <h4>Here are edited names of feedback stages that admin see in the admin panel</h4>
            </div>
            <?php $pjax = Pjax::begin([
                'options' => [
                    'id' => 'edit-feedback-stages-names-container',
                ]
            ]) ?>
            <?php $form = ActiveForm::begin([
                'id' => 'edit-feedback-stages-names-form',
                'options' => [
                    'data-pjax' => true,
                ],
            ]);
            ?>

            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong>Success!</strong> Feedback stage data updated.
                </div>
            <?php endif; ?>

            <?= SimpleTabsTranslatesWidget::widget([
                'form'            => $form,
                'translateModels' => $translateModels,
            ])
            ?>

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
