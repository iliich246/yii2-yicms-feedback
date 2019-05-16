<?php //template

use yii\helpers\Url;
use yii\widgets\Pjax;
use Iliich246\YicmsCommon\CommonModule;
use Iliich246\YicmsFeedback\FeedbackModule;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $feedbackState \Iliich246\YicmsFeedback\Base\FeedbackState */
/** @var $inputFieldTemplates \Iliich246\YicmsFeedback\InputFields\InputFieldTemplate[] */
/** @var $inputFilesTemplates \Iliich246\YicmsFeedback\InputFiles\InputFilesBlock[] */
/** @var $inputImagesTemplates \Iliich246\YicmsFeedback\InputImages\InputImagesBlock[] */
/** @var $inputConditionsTemplates \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate[] */

$js = <<<JS
;(function() {
    $('#state-delete').on('click',  function() {
        var button = this;

        if (!$(button).is('[data-state-id]')) return;
        
        var stateId    = $(button).data('stateId');
        var feedbackId = $(button).data('feedbackId');  
        var homeUrl    = $(button).data('homeUrl');
        var deleteUrl  = homeUrl + '/feedback/admin/delete-state';
        
        if (!($(this).hasClass('stage-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('stage-confirm-state');
        } else {
            window.location.href = 
                deleteUrl + '?feedbackId=' + feedbackId + '&stateId=' + stateId;
        }
    });    
})();
JS;

$this->registerJs($js, $this::POS_READY);
?>


<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1><?= FeedbackModule::t('app', 'View Message') ?></h1>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['elements-list', 'feedbackId' => $feedback->id]) ?>"><span>Feedback list</span></a>
        <span> / </span>
        <span>View message</span>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">

            <h3>Message by</h3>
            <h4><?= Yii::$app->formatter->asDatetime($feedbackState->created_at) ?></h4>
            <br>

            <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/input-fields/input-fields-block', [
                'feedback' => $feedback,
                'inputFieldTemplates' => $inputFieldTemplates,
            ]) ?>

            <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/input-files/input-files-block', [
                'feedback' => $feedback,
                'inputFilesTemplates' => $inputFilesTemplates,
            ]) ?>

            <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/input-images/input-images-block', [
                'feedback' => $feedback,
                'inputImagesTemplates' => $inputImagesTemplates,
            ]) ?>

            <?= $this->render(CommonModule::getInstance()->yicmsLocation . '/Feedback/Views/input-conditions/input-conditions-block', [
                'feedback' => $feedback,
                'inputConditionsTemplates' => $inputConditionsTemplates,
            ]) ?>

        </div>
        
        <br>
        <hr>
        <br>

        <div class="col-xs-12">
            <?php if ($feedbackState->isViewed()): ?>
                <a href="<?= Url::toRoute(['change-state-viewed',
                    'feedbackId' => $feedback->id,
                    'stateId'    => $feedbackState->id,
                ]) ?>">
                    <button type="button" class="btn btn-default">
                        Mark as none viewed
                    </button>
                </a>
            <?php else: ?>
                <a href="<?= Url::toRoute(['change-state-viewed',
                    'feedbackId' => $feedback->id,
                    'stateId'    => $feedbackState->id,
                ]) ?>">
                    <button type="button" class="btn btn-success">
                        Mark as viewed
                    </button>
                </a>
            <?php endif; ?>
        </div>
        <div class="col-xs-12">
            <?php if (true == true): ?>
                <div class="row delete-button-row-page">
                    <div class="col-xs-12">
                        <br>
                        <button type="button"
                                class="btn btn-danger"
                                data-home-url="<?= \yii\helpers\Url::base() ?>"
                                data-feedback-id="<?= $feedback->id ?>"
                                data-state-id="<?= $feedbackState->id ?>"
                                id="state-delete">
                            Delete message
                        </button>
                        <br>
                        <br>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
