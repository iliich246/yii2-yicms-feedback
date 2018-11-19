<?php

use yii\helpers\Url;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;
use Iliich246\YicmsFeedback\InputImages\InputImagesDevModalWidget;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $feedbackStage \Iliich246\YicmsFeedback\Base\FeedbackStages */
/** @var $devInputFieldGroup \Iliich246\YicmsFeedback\InputFields\DevInputFieldsGroup */
/** @var $inputFieldTemplates InputFieldTemplate[] */
/** @var $inputFilesBlocks \Iliich246\YicmsFeedback\InputFiles\InputFilesBlock[] */
/** @var $devInputFilesGroup \Iliich246\YicmsFeedback\InputFiles\DevInputFilesGroup */
/** @var $inputImagesBlocks \Iliich246\YicmsFeedback\InputImages\InputImagesBlock[] */
/** @var $devInputImagesGroup \Iliich246\YicmsFeedback\InputImages\DevInputImagesGroup */
/** @var $devInputConditionsGroup \Iliich246\YicmsFeedback\InputConditions\DevInputConditionsGroup */
/** @var $inputConditionTemplates \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate[] */
/** @var $success bool */

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>Edit feedback stage (<?= $feedbackStage->program_name ?>) input templates</h1>
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

        <a href="<?= Url::toRoute(['update-stage', 'id' => $feedbackStage->id]) ?>">
            <span>Update feedback stage</span>
        </a>

        <span> / </span>

        <span>Edit feedback stage (<?= $feedbackStage->program_name ?>) input templates</span>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">

            <div class="content-block-title">
                <h3>Edit feedback stage input templates</h3>
            </div>

        </div>
    </div>

    <?= $this->render('@yicms-feedback/views/pjax/update-input-fields-list-container', [
        'inputFieldTemplateReference' => $feedbackStage->getInputFieldTemplateReference(),
        'inputFieldTemplates'         => $inputFieldTemplates
    ]) ?>

    <?= InputFieldsDevModalWidget::widget([
        'devInputFieldGroup' => $devInputFieldGroup,
    ])

    ?>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-files-list-container', [
        'inputFileTemplateReference' => $feedbackStage->getInputFieldTemplateReference(),
        'inputFilesBlocks'           => $inputFilesBlocks,
    ]) ?>

    <?= InputFilesDevModalWidget::widget([
        'devInputFilesGroup' => $devInputFilesGroup,
        'action'             => Url::toRoute(['/feedback/dev/stage-input-templates',
            'id' => $feedbackStage->id])
    ]) ?>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-images-list-container', [
        'inputImageTemplateReference' => $feedbackStage->getInputImageTemplateReference(),
        'inputImagesBlocks'           => $inputImagesBlocks,
    ]) ?>

    <?= InputImagesDevModalWidget::widget([
        'devInputImagesGroup' => $devInputImagesGroup,
        'action'              => Url::toRoute(['/feedback/dev/stage-input-templates',
            'id' => $feedbackStage->id])
    ]) ?>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-conditions-list-container', [
        'inputConditionTemplateReference' => $feedbackStage->getInputConditionTemplateReference(),
        'inputConditionsTemplates'        => $inputConditionTemplates,
    ]) ?>

    <?= InputConditionsDevModalWidget::widget([
        'devInputConditionsGroup' => $devInputConditionsGroup,
        'action' => Url::toRoute(['/feedback/dev/stage-page-templates',
            'id' => $feedbackStage->id])
    ]) ?>
</div>
