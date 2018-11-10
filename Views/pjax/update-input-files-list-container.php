<?php

use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $inputFileTemplateReference string */
/* @var $inputFilesBlocks \Iliich246\YicmsFeedback\InputFiles\InputFilesBlock[] */

$js = <<<JS
;(function() {
    
})();
JS;

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);
$src = $bundle->baseUrl . '/loader.svg';

?>

<div class="row content-block form-block">
    <div class="col-xs-12">
        <div class="content-block-title">
            <h3>List of input files</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-input-file"
                        data-toggle="modal"
                        data-target="#inputFileDevModal"
                        data-input-file-template-reference="<?= $inputFileTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= InputFilesDevModalWidget::getPjaxContainerId() ?>"
                        data-fields-modal-name="<?= InputFilesDevModalWidget::getModalWindowName() ?>"
                        data-loader-image-src="<?= $src ?>"
                        data-current-selected-input-file-template="null"
                >
                    <span class="glyphicon glyphicon-plus-sign"></span> Add new input file
                </button>
            </div>
        </div>
        <?php if (isset($inputFilesBlocks)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-input-files-list-container'
                ]
            ]) ?>

            <div class="list-block">
                <div class="row content-block-title">

                </div>
                <?php foreach ($inputFilesBlocks as $inputFilesBlock): ?>
                    <div class="row list-items input-field-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-file-template="<?= $inputFilesBlock->input_file_template_reference ?>"
                               data-input-file-template-id="<?= $inputFilesBlock->id ?>"
                            >
                                <?= $inputFilesBlock->program_name ?>
                            </p>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($inputFilesBlock->visible): ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->editable): ?>
                                <span class="glyphicon glyphicon-pencil"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->canUpOrder()): ?>
                                <span class="glyphicon field-arrow-up glyphicon-arrow-up"
                                      data-input-file-template-id="<?= $inputFilesBlock->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->canDownOrder()): ?>
                                <span class="glyphicon field-arrow-down glyphicon-arrow-down"
                                      data-input-file-template-id="<?= $inputFilesBlock->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
