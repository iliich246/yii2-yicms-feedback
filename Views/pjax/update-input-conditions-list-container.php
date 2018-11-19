<?php

use yii\widgets\Pjax;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

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
        <?php if (isset($inputConditionTemplates)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-input-conditions-list-container'
                ]
            ]) ?>

            <div class="list-block">
                <div class="row content-block-title">

                </div>
                <?php foreach ($inputConditionTemplates as $inputConditionsTemplate): ?>
                    <div class="row list-items input-file-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-condition-template="<?= $inputConditionsTemplate->input_condition_template_reference ?>"
                               data-input-condition-template-id="<?= $inputConditionsTemplate->id ?>"
                                >
                                <?= $inputConditionsTemplate->program_name ?>
                            </p>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($inputConditionsTemplate->visible): ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                            <?php if ($inputConditionsTemplate->editable): ?>
                                <span class="glyphicon glyphicon-pencil"></span>
                            <?php endif; ?>
                            <?php if ($inputConditionsTemplate->canUpOrder()): ?>
                                <span class="glyphicon file-arrow-up glyphicon-arrow-up"
                                      data-input-condition-template-id="<?= $inputConditionsTemplate->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputConditionsTemplate->canDownOrder()): ?>
                                <span class="glyphicon file-arrow-down glyphicon-arrow-down"
                                      data-input-condition-template-id="<?= $inputConditionsTemplate->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
