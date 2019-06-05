<?php

use yii\helpers\Url;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;
use Iliich246\YicmsFeedback\InputImages\InputImagesDevModalWidget;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
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
        <h1>Edit feedback stage (<?= $feedback->program_name ?>) input templates</h1>
    </div>

    <div class="row content-block breadcrumbs">
        <a href="<?= Url::toRoute(['list']) ?>">
            <span>Feedback list</span>
        </a>

        <span> / </span>

        <a href="<?= Url::toRoute(['update-feedback', 'id' => $feedback->id]) ?>">
            <span>Feedback update</span>
        </a>

        <span> / </span>

        <span>Edit feedback stage (<?= $feedback->program_name ?>) input templates</span>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">

            <div class="content-block-title">
                <h3>Edit feedback input templates</h3>
            </div>

        </div>
    </div>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-fields-list-container', [
        'inputFieldTemplateReference' => $feedback->getInputFieldTemplateReference(),
        'inputFieldTemplates'         => $inputFieldTemplates
    ]) ?>

    <?= InputFieldsDevModalWidget::widget([
        'devInputFieldGroup' => $devInputFieldGroup,
    ])

    ?>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-files-list-container', [
        'inputFileTemplateReference' => $feedback->getInputFileTemplateReference(),
        'inputFilesBlocks'           => $inputFilesBlocks,
    ]) ?>

    <?= InputFilesDevModalWidget::widget([
        'devInputFilesGroup' => $devInputFilesGroup,
        'action'             => Url::toRoute(['/feedback/dev/stage-input-templates',
            'id' => $feedback->id])
    ]) ?>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-images-list-container', [
        'inputImageTemplateReference' => $feedback->getInputImageTemplateReference(),
        'inputImagesBlocks'           => $inputImagesBlocks,
    ]) ?>

    <?= InputImagesDevModalWidget::widget([
        'devInputImagesGroup' => $devInputImagesGroup,
        'action'              => Url::toRoute(['/feedback/dev/stage-input-templates',
            'id' => $feedback->id])
    ]) ?>

    <?= $this->render('@yicms-feedback/Views/pjax/update-input-conditions-list-container', [
        'inputConditionTemplateReference' => $feedback->getInputConditionTemplateReference(),
        'inputConditionTemplates'         => $inputConditionTemplates,
    ]) ?>

    <?= InputConditionsDevModalWidget::widget([
        'devInputConditionsGroup' => $devInputConditionsGroup,
        'action' => Url::toRoute(['/feedback/dev/stage-page-templates',
            'id' => $feedback->id])
    ]) ?>
</div>
