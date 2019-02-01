<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;
use Iliich246\YicmsCommon\Validators\ValidatorsListWidget;
use Iliich246\YicmsFeedback\InputConditions\DevInputConditionsGroup;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $widget \Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget */
/** @var $bundle \Iliich246\YicmsCommon\Assets\DeveloperAsset */

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);

$modalName = InputConditionsDevModalWidget::getModalWindowName();
$deleteLink = $widget->deleteLink . '?inputConditionTemplateId=';

$js = <<<JS
;(function() {
    $(document).on('click', '#input-condition-delete', function() {
        var button = ('#input-condition-delete');

        if (!$(button).is('[data-input-condition-template-id]')) return;

        var inputConditionTemplateId     = $(button).data('inputConditionTemplateId');
        var inputConditionHasConstraints = $(button).data('inputConditionHasConstraints');
        var pjaxContainer                = $('#update-input-conditions-list-container');

        if (!($(this).hasClass('input-condition-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('input-condition-confirm-state');
        } else {
            if (!inputConditionHasConstraints) {
                 $.pjax({
                    url: '{$deleteLink}' + inputConditionTemplateId,
                    container: '#update-input-conditions-list-container',
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
                var deleteButtonRow = $('.delete-button-row-input-condition');

                var template = _.template($('#delete-with-pass-template-input-condition').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);

                var passwordInput = $('#input-condition-delete-password-input');
                var buttonDelete  = $('#button-delete-with-pass-input-condition');

                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: '{$deleteLink}' + inputConditionTemplateId + '&deletePass=' + $(passwordInput).val(),
                        container: '#update-input-conditions-list-container',
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
                            message: "Input condition template has not deleted",
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

$this->registerAssetBundle(\Iliich246\YicmsCommon\Assets\LodashAsset::className());

if ($widget->devInputConditionsGroup->scenario == DevInputConditionsGroup::SCENARIO_CREATE &&
    $widget->devInputConditionsGroup->justSaved)
    $redirectToUpdate = 'true';
else
    $redirectToUpdate = 'false';

if ($redirectToUpdate == 'true')
    $inputConditionTemplateIdForRedirect = $widget->devInputConditionsGroup->inputConditionTemplate->id;
else
    $inputConditionTemplateIdForRedirect = '0';

?>

<div class="modal fade"
     id="<?= InputConditionsDevModalWidget::getModalWindowName() ?>"
     tabindex="-1"
     role="dialog"
     data-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <?php Pjax::begin([
            'options' => [
                'id'                         => InputConditionsDevModalWidget::getPjaxContainerId(),
                'class'                      => 'pjax-container',
                'data-return-url'            => '0',
                'data-return-url-validators' => '0',
            ],
        ]); ?>
        <?php $form = ActiveForm::begin([
            'id'      => InputConditionsDevModalWidget::getFormName(),
            'action'  => $widget->action,
            'options' => [
                'data-pjax'                                 => true,
                'data-yicms-saved'                          => $widget->dataSaved,
                'data-save-and-exit'                        => $widget->saveAndExit,
                'data-redirect-to-update-input-condition'   => $redirectToUpdate,
                'data-input-condition-template-id-redirect' => $inputConditionTemplateIdForRedirect
            ],
        ]);
        ?>

        <?php if ($widget->devInputConditionsGroup->scenario == DevInputConditionsGroup::SCENARIO_UPDATE): ?>
            <?= Html::hiddenInput('_inputConditionBlockId', $widget->devInputConditionsGroup->inputConditionTemplate->id, [
                'id' => 'input-condition-block-id-hidden'
            ]) ?>
        <?php endif; ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" id="myModalLabel">
                    <?php if ($widget->devInputConditionsGroup->scenario == DevInputConditionsGroup::SCENARIO_CREATE): ?>
                        Create new input condition
                    <?php else: ?>
                        Update existed input condition (<?= $widget->devInputConditionsGroup->inputConditionTemplate->program_name ?>)
                        <?= $widget->devInputConditionsGroup->inputConditionTemplate->id ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <?= $form->field($widget->devInputConditionsGroup->inputConditionTemplate, 'program_name') ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputConditionsGroup->inputConditionTemplate, 'active')->checkbox() ?>
                    </div>
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputConditionsGroup->inputConditionTemplate, 'editable')->checkbox() ?>
                    </div>
                </div>

                <?= SimpleTabsTranslatesWidget::widget([
                    'form'            => $form,
                    'translateModels' => $widget->devInputConditionsGroup->conditionNameTranslates,
                ])
                ?>

                <?php if ($widget->devInputConditionsGroup->scenario == DevInputConditionsGroup::SCENARIO_UPDATE): ?>
                    <div class="row delete-button-row-input-condition">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input conditions template without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="input-condition-delete"
                                    data-input-condition-template-reference="
                                    <?= $widget->devInputConditionsGroup->inputConditionTemplate->input_condition_template_reference ?>"
                                    data-input-condition-template-id="<?= $widget->devInputConditionsGroup->inputConditionTemplate->id ?>"
                                    data-input-condition-has-constraints="<?= (int)$widget->devInputConditionsGroup->inputConditionTemplate->isConstraints() ?>"
                            >
                                Delete input condition
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template-input-condition">
                        <div class="col-xs-12">
                            <br>
                            <label for="input-condition-delete-password-input">
                                Input condition has constraints. Enter dev password for delete input condition template
                            </label>
                            <input type="password"
                                   id="input-condition-delete-password-input"
                                   class="form-control" name=""
                                   value=""
                                   aria-required="true"
                                   aria-invalid="false">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="button-delete-with-pass-input-condition"
                            >
                                Yes, i am absolutely seriously!!!
                            </button>
                        </div>
                    </script>

                    <hr>

                    <p class="btn btn-primary input-condition-data-list"
                       data-input-condition-template-id="<?= $widget->devInputConditionsGroup->inputConditionTemplate->id ?>"
                       data-return-url-conditions-list="<?= \yii\helpers\Url::toRoute([
                           '/feedback/dev-input-conditions/load-modal',
                           'inputConditionTemplateId' => $widget->devInputConditionsGroup->inputConditionTemplate->id,
                       ]) ?>"
                        >
                        Config input condition options
                    </p>

                    <hr>

                    <?= ValidatorsListWidget::widget([
                        'validatorReference'     => $widget->devInputConditionsGroup->inputConditionTemplate,
                        'ownerPjaxContainerName' => InputConditionsDevModalWidget::getPjaxContainerId(),
                        'ownerModalId'           => InputConditionsDevModalWidget::getModalWindowName(),
                        'returnUrl'              => \yii\helpers\Url::toRoute([
                            '/feedback/dev-input-conditions/load-modal',
                            'inputConditionTemplateId' => $widget->devInputConditionsGroup->inputConditionTemplate->id,
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
