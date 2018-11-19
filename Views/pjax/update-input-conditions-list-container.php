<?php

use yii\widgets\Pjax;

/** @var $this \yii\web\View */
/** @var $inputConditionTemplateReference string */
/** @var $inputConditionTemplates \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate[] */

$js = <<<JS
;(function() {

})();
JS;

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);
$src = $bundle->baseUrl . '/loader.svg';

$this->registerJs($js, $this::POS_READY);

?>

<div class="row content-block form-block">
    <div class="col-xs-12">
        <div class="content-block-title">
            <h3>List of input conditions</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-input-condition"
                        data-toggle="modal"
                        data-target="#inputConditionsDevModal"
                        data-input-condition-template-reference="<?= $inputConditionTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= InputConditionsDevModalWidget::getPjaxContainerId() ?>"
                        data-conditions-modal-name="<?= InputConditionsDevModalWidget::getModalWindowName() ?>"
                        data-loader-image-src="<?= $src ?>"
                        data-current-selected-input-file-block="null"
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
                    <div class="row list-items input-file-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-file-block="<?= $inputFilesBlock->input_file_template_reference ?>"
                               data-input-file-block-id="<?= $inputFilesBlock->id ?>"
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
                                <span class="glyphicon file-arrow-up glyphicon-arrow-up"
                                      data-input-file-block-id="<?= $inputFilesBlock->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->canDownOrder()): ?>
                                <span class="glyphicon file-arrow-down glyphicon-arrow-down"
                                      data-input-file-block-id="<?= $inputFilesBlock->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>