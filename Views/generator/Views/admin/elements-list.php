<?php //template

use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;
use Iliich246\YicmsFeedback\FeedbackModule;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $feedbackStates \Iliich246\YicmsFeedback\Base\FeedbackState[] */
/** @var $pagination \yii\data\Pagination */

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1><?= FeedbackModule::t('app', 'List of feedback') ?></h1>
    </div>
    <div class="row content-block">
        <div class="col-xs-12">
            <?php  Pjax::begin([
                'options' => [
                    'class' => 'pjax-container',
                    'id'    => 'feedback-list-container'
                ],
                'enablePushState'    => false,
                'enableReplaceState' => false
            ]); ?>

            <div class="list-block">
                <?php foreach ($feedbackStates as $feedbackState): ?>
                    <?php $feedback->setActiveState($feedbackState) ?>
                    <div class="row list-items">
                        <div class="col-xs-6 list-title">
                            <a data-pjax="0"
                               href="<?= Url::toRoute(['/feedback/admin/view-state',
                                   'feedbackId' => $feedback->id,
                                   'stateId'    => $feedbackState->id
                               ]) ?>">
                                <p>
                                    <?= $feedback->stateAdminName() ?>
                                </p>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?= LinkPager::widget([
                    'pagination' => $pagination,
                ]); ?>

            </div>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
