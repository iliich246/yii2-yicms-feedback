<?php

use yii\helpers\Url;
use Iliich246\YicmsCommon\Fields\FieldTemplate;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;
use Iliich246\YicmsCommon\Files\FilesDevModalWidget;
use Iliich246\YicmsCommon\Images\ImagesDevModalWidget;
use Iliich246\YicmsCommon\Conditions\ConditionsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $devFieldGroup \Iliich246\YicmsCommon\Fields\DevFieldsGroup */
/** @var $fieldTemplatesTranslatable FieldTemplate[] */
/** @var $fieldTemplatesSingle FieldTemplate[] */
/** @var $filesBlocks \Iliich246\YicmsCommon\Files\FilesBlock[] */
/** @var $devFilesGroup \Iliich246\YicmsCommon\Files\DevFilesGroup */
/** @var $imagesBlocks \Iliich246\YicmsCommon\Images\ImagesBlock[] */
/** @var $devImagesGroup \Iliich246\YicmsCommon\Images\DevImagesGroup */
/** @var $devConditionsGroup Iliich246\YicmsCommon\Conditions\DevConditionsGroup */
/** @var $conditionTemplates Iliich246\YicmsCommon\Conditions\ConditionTemplate[] */
/** @var $success bool */

?>

<div class="col-sm-9 content">
    <div class="row content-block content-header">
        <h1>Edit feedback stage (<?= $feedback->program_name ?>) page templates</h1>
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

        <span>Edit feedback (<?= $feedback->program_name ?>) page templates</span>
    </div>

    <div class="row content-block form-block">
        <div class="col-xs-12">

            <div class="content-block-title">
                <h3>Edit feedback templates</h3>
            </div>

        </div>
    </div>

    <?= $this->render('@yicms-common/Views/pjax/update-fields-list-container', [
        'fieldTemplateReference'     => $feedback->getFieldTemplateReference(),
        'fieldTemplatesTranslatable' => $fieldTemplatesTranslatable,
        'fieldTemplatesSingle'       => $fieldTemplatesSingle
    ]) ?>

    <?= FieldsDevModalWidget::widget([
        'devFieldGroup' => $devFieldGroup,
    ])
    ?>

    <?= $this->render('@yicms-common/Views/pjax/update-files-list-container', [
        'fileTemplateReference' => $feedback->getFileTemplateReference(),
        'filesBlocks'           => $filesBlocks,
    ]) ?>

    <?= FilesDevModalWidget::widget([
        'devFilesGroup' => $devFilesGroup,
        'action' => Url::toRoute(['/feedback/dev/stage-page-templates',
            'id' => $feedback->id])
    ]) ?>

    <?= $this->render('@yicms-common/Views/pjax/update-images-list-container', [
        'imageTemplateReference' => $feedback->getImageTemplateReference(),
        'imagesBlocks'           => $imagesBlocks,
    ]) ?>

    <?= ImagesDevModalWidget::widget([
        'devImagesGroup' => $devImagesGroup,
        'action' => Url::toRoute(['/feedback/dev/stage-page-templates',
            'id' => $feedback->id])
    ]) ?>

    <?= $this->render('@yicms-common/Views/pjax/update-conditions-list-container', [
        'conditionTemplateReference' => $feedback->getConditionTemplateReference(),
        'conditionsTemplates'        => $conditionTemplates,
    ]) ?>

    <?= ConditionsDevModalWidget::widget([
        'devConditionsGroup' => $devConditionsGroup,
        'action' => Url::toRoute(['/feedback/dev/stage-page-templates',
            'id' => $feedback->id])
    ]) ?>
</div>
