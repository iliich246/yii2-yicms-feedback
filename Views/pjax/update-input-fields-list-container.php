<?php

use yii\widgets\Pjax;
use Iliich246\YicmsCommon\Assets\FieldsDevAsset;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $fieldTemplateReference integer */

FieldsDevAsset::register($this);

?>

<div class="row content-block form-block">
    <div class="col-xs-12">
        <div class="content-block-title">
            <h3>List of input fields</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-field"
                        data-toggle="modal"
                        data-target="#fieldsDevModal"
                        data-field-template-reference="<?= $fieldTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= FieldsDevModalWidget::getPjaxContainerId() ?>"
                        data-fields-modal-name="<?= FieldsDevModalWidget::getModalWindowName() ?>"
                        data-current-selected-field-template="null"
                    >
                    <span class="glyphicon glyphicon-plus-sign"></span> Add new field
                </button>
            </div>
        </div>
        <?php if (isset($fieldTemplatesTranslatable) || isset($fieldTemplatesSingle)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-fields-list-container'
                ]
            ]) ?>
            <div class="list-block">
                <?php if (isset($fieldTemplatesSingle)): ?>
                    <div class="row content-block-title">

                    </div>
                    <?php foreach ($fieldTemplatesSingle as $fieldTemplate): ?>
                        <div class="row list-items field-item">
                            <div class="col-xs-10 list-title">
                                <p data-field-template="<?= $fieldTemplate->field_template_reference ?>"
                                   data-field-template-id="<?= $fieldTemplate->id ?>"
                                    >
                                    <?= $fieldTemplate->program_name ?> (<?= $fieldTemplate->getTypeName() ?>)
                                </p>
                            </div>
                            <div class="col-xs-2 list-controls">
                                <?php if ($fieldTemplate->visible): ?>
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                <?php else: ?>
                                    <span class="glyphicon glyphicon-eye-close"></span>
                                <?php endif; ?>
                                <?php if ($fieldTemplate->editable): ?>
                                    <span class="glyphicon glyphicon-pencil"></span>
                                <?php endif; ?>
                                <?php if ($fieldTemplate->canUpOrder()): ?>
                                    <span class="glyphicon field-arrow-up glyphicon-arrow-up"
                                          data-field-template-id="<?= $fieldTemplate->id ?>"></span>
                                <?php endif; ?>
                                <?php if ($fieldTemplate->canDownOrder()): ?>
                                    <span class="glyphicon field-arrow-down glyphicon-arrow-down"
                                          data-field-template-id="<?= $fieldTemplate->id ?>"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
