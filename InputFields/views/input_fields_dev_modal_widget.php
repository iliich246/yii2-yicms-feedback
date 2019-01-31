<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;
use Iliich246\YicmsCommon\Validators\ValidatorsListWidget;
use Iliich246\YicmsFeedback\InputFields\InputFieldTemplate;
use Iliich246\YicmsFeedback\InputFields\DevInputFieldsGroup;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $widget InputFieldsDevModalWidget */

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);

$modalName = InputFieldsDevModalWidget::getModalWindowName();
$deleteLink = $widget->deleteLink . '?inputFieldTemplateId=';

$js = <<<JS
;(function() {
    $(document).on('click', '#input-field-delete', function() {
        var button = ('#input-field-delete');

        if (!$(button).is('[data-input-field-template-id]')) return;

        var inputFieldTemplateId     = $(button).data('inputFieldTemplateId');
        var inputFieldHasConstraints = $(button).data('inputFieldHasConstraints');
        var pjaxContainer       = $('#update-input-fields-list-container');

        if (!($(this).hasClass('input-field-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('input-field-confirm-state');
        } else {
            if (!inputFieldHasConstraints) {
                $.pjax({
                    url: '{$deleteLink}' + inputFieldTemplateId,
                    container: '#update-input-fields-list-container',
                    scrollTo: false,
                    push: false,
                    type: "POST",
                    timeout: 2500
                });

                var deleteActive = true;

                $(pjaxContainer).on('pjax:success', function(event) {

                    if (!deleteActive) return false;

                    $('#{$modalName}').modal('hide');
                    deleteActive = false;
                });
            } else {
                var deleteButtonRow = $('.delete-button-row-field');

                var template = _.template($('#delete-with-pass-template').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);

                var passwordInput = $('#input-field-delete-password-input');
                var buttonDelete  = $('#input-field-button-delete-with-pass');

                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: '{$deleteLink}' + inputFieldTemplateId + '&deletePass=' + $(passwordInput).val(),
                        container: '#update-input-fields-list-container',
                        scrollTo: false,
                        push: false,
                        type: "POST",
                        timeout: 2500
                    });

                    var deleteActive = true;

                    $(pjaxContainer).on('pjax:success', function(event) {

                        if (!deleteActive) return false;

                        $('#{$modalName}').modal('hide');
                        deleteActive = false;
                    });

                    $(pjaxContainer).on('pjax:error', function(event) {

                        $('#{$modalName}').modal('hide');

                        bootbox.alert({
                            size: 'large',
                            title: "Wrong dev password",
                            message: "Field template has not deleted",
                            className: 'bootbox-error'
                        });
                    });
                });

                $('#{$modalName}').on('hide.bs.modal', function() {
                    $(pjaxContainer).off('pjax:error');
                    $(pjaxContainer).off('pjax:success');
                    $('#{$modalName}').off('hide.bs.modal');
                });
            }
        }
    });
})();
JS;

$this->registerJs($js, $this::POS_READY);

if ($widget->devInputFieldGroup->scenario == DevInputFieldsGroup::SCENARIO_CREATE &&
    $widget->devInputFieldGroup->justSaved)
    $redirectToUpdate = 'true';
else
    $redirectToUpdate = 'false';

if ($redirectToUpdate == 'true')
    $inputFieldTemplateIdForRedirect = $widget->devInputFieldGroup->inputFieldTemplate->id;
else
    $inputFieldTemplateIdForRedirect = '0';

?>

<div class="modal fade"
     id="<?= InputFieldsDevModalWidget::getModalWindowName() ?>"
     tabindex="-1"
     role="dialog"
     data-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <?php Pjax::begin([
            'options' => [
                'id'                         => InputFieldsDevModalWidget::getPjaxContainerId(),
                'class'                      => 'pjax-container',
                'data-return-url'            => '0',
                'data-return-url-validators' => '0',
            ],
        ]); ?>
        <?php $form = ActiveForm::begin([
            'id'      => InputFieldsDevModalWidget::getFormName(),
            'action'  => $widget->action,
            'options' => [
                'data-pjax'                             => true,
                'data-yicms-saved'                      => $widget->dataSaved,
                'data-save-and-exit'                    => $widget->saveAndExit,
                'data-redirect-to-update-input-field'   => $redirectToUpdate,
                'data-input-field-template-id-redirect' => $inputFieldTemplateIdForRedirect
            ],
        ]);
        ?>

        <?php if ($widget->devInputFieldGroup->scenario == DevInputFieldsGroup::SCENARIO_UPDATE): ?>
            <?= Html::hiddenInput('_inputFieldTemplateId', $widget->devInputFieldGroup->inputFieldTemplate->id, [
                'id' => 'input-field-template-id-hidden'
            ]) ?>
        <?php endif; ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" id="myModalLabel">
                    <?php if ($widget->devInputFieldGroup->scenario == DevInputFieldsGroup::SCENARIO_CREATE): ?>
                        Create new field
                    <?php else: ?>
                        Update existed field (<?= $widget->devInputFieldGroup->inputFieldTemplate->program_name ?>)
                        <?= $widget->devInputFieldGroup->inputFieldTemplate->id ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <?= $form->field($widget->devInputFieldGroup->inputFieldTemplate, 'program_name') ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFieldGroup->inputFieldTemplate, 'active')->checkbox() ?>
                    </div>
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFieldGroup->inputFieldTemplate, 'editable')->checkbox() ?>
                    </div>
                </div>

                <?= SimpleTabsTranslatesWidget::widget([
                    'form'            => $form,
                    'translateModels' => $widget->devInputFieldGroup->fieldNameTranslates,
                ])
                ?>

                <?php if ($widget->devInputFieldGroup->scenario == DevInputFieldsGroup::SCENARIO_UPDATE): ?>
                    <div class="row delete-button-row-field">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input fields without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="input-field-delete"
                                    data-input-field-template-reference="<?= $widget->devInputFieldGroup->inputFieldTemplate->input_field_template_reference ?>"
                                    data-input-field-template-id="<?= $widget->devInputFieldGroup->inputFieldTemplate->id ?>"
                                    data-input-field-has-constraints="<?= (int)$widget->devInputFieldGroup->inputFieldTemplate->isConstraints() ?>"
                            >
                                Delete input field
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template">
                        <div class="col-xs-12">
                            <br>
                            <label for="input-field-delete-password-input">
                                Input field has constraints. Enter dev password for delete input field template
                            </label>
                            <input type="password"
                                   id="input-field-delete-password-input"
                                   class="form-control" name=""
                                   value=""
                                   aria-required="true"
                                   aria-invalid="false">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="input-field-button-delete-with-pass"
                            >
                                Yes, i am absolutely seriously!!!
                            </button>
                        </div>
                    </script>

                    <hr>

                    <?= ValidatorsListWidget::widget([
                        'validatorReference'     => $widget->devInputFieldGroup->inputFieldTemplate,
                        'ownerPjaxContainerName' => InputFieldsDevModalWidget::getPjaxContainerId(),
                        'ownerModalId'           => InputFieldsDevModalWidget::getModalWindowName(),
                        'returnUrl'              => \yii\helpers\Url::toRoute([
                            '/feedback/dev-input-fields/load-modal',
                            'inputFieldTemplateId' => $widget->devInputFieldGroup->inputFieldTemplate->id,
                        ])
                    ]) ?>

                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <?= Html::submitButton('Save and exit', ['class' => 'btn btn-success',
                    'value' => 'true', 'name' => '_saveAndExit']) ?>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end() ?>
    </div>
</div>

