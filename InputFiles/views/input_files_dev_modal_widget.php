<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;
use Iliich246\YicmsCommon\Validators\ValidatorsListWidget;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;

/** @var $this \yii\web\View */
/** @var $widget InputFilesDevModalWidget */

if ($widget->devInputFilesGroup->scenario == DevInputFieldsGroup::SCENARIO_CREATE &&
    $widget->devInputFilesGroup->justSaved)
    $redirectToUpdate = 'true';
else
    $redirectToUpdate = 'false';

if ($redirectToUpdate == 'true')
    $inputFileTemplateIdForRedirect = $widget->devInputFilesGroup->inputFileTemplate->id;
else
    $inputFileTemplateIdForRedirect = '0';

?>

<div class="modal fade"
     id="<?= InputFilesDevModalWidget::getModalWindowName() ?>"
     tabindex="-1"
     role="dialog"
     data-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <?php Pjax::begin([
            'options' => [
                'id'                         => InputFilesDevModalWidget::getPjaxContainerId(),
                'class'                      => 'pjax-container',
                'data-return-url'            => '0',
                'data-return-url-validators' => '0',
            ],
        ]); ?>
        <?php $form = ActiveForm::begin([
            'id'      => InputFilesDevModalWidget::getFormName(),
            'action'  => $widget->action,
            'options' => [
                'data-pjax'                       => true,
                'data-yicms-saved'                => $widget->dataSaved,
                'data-save-and-exit'              => $widget->saveAndExit,
                'data-redirect-to-update-input-file'   => $redirectToUpdate,
                'data-input-file-template-id-redirect' => $inputFileTemplateIdForRedirect
            ],
        ]);
        ?>

        <?php if ($widget->devInputFilesGroup->scenario == DevInputFilesGroup::SCENARIO_UPDATE): ?>
            <?= Html::hiddenInput('_inputFileTemplateId', $widget->devInputFilesGroup->inputFileTemplate->id, [
                'id' => 'input-file-template-id-hidden'
            ]) ?>
        <?php endif; ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" id="myModalLabel">
                    <?php if ($widget->devInputFilesGroup->scenario == DevInputFilesGroup::SCENARIO_CREATE): ?>
                        Create new field
                    <?php else: ?>
                        Update existed field (<?= $widget->devInputFilesGroup->inputFileTemplate->program_name ?>)
                        <?= $widget->devInputFilesGroup->inputFileTemplate->id ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <?= $form->field($widget->devInputFilesGroup->inputFileTemplate, 'program_name') ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFilesGroup->inputFileTemplate, 'visible')->checkbox() ?>
                    </div>
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFilesGroup->inputFileTemplate, 'editable')->checkbox() ?>
                    </div>
                </div>

                <?= SimpleTabsTranslatesWidget::widget([
                    'form'            => $form,
                    'translateModels' => $widget->devInputFilesGroup->fieldNameTranslates,
                ])
                ?>

                <?php if ($widget->devInputFilesGroup->scenario == DevInputFieldsGroup::SCENARIO_UPDATE): ?>
                    <div class="row delete-button-row-field">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input fields without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="field-delete"
                                    data-input-field-template-reference="<?= $widget->devInputFilesGroup->inputFileTemplate->input_field_template_reference ?>"
                                    data-input-field-template-id="<?= $widget->devInputFilesGroup->inputFileTemplate->id ?>"
                                    data-input-field-has-constraints="<?= (int)$widget->devInputFilesGroup->inputFileTemplate->isConstraints() ?>"
                            >
                                Delete input field
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template">
                        <div class="col-xs-12">
                            <br>
                            <label for="field-delete-password-input">
                                Input field has constraints. Enter dev password for delete input field template
                            </label>
                            <input type="password"
                                   id="field-delete-password-input"
                                   class="form-control" name=""
                                   value=""
                                   aria-required="true"
                                   aria-invalid="false">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="button-delete-with-pass"
                            >
                                Yes, i am absolutely seriously!!!
                            </button>
                        </div>
                    </script>

                    <hr>

                    <?= ValidatorsListWidget::widget([
                        'validatorReference'     => $widget->devInputFilesGroup->inputFileTemplate,
                        'ownerPjaxContainerName' => InputFieldsDevModalWidget::getPjaxContainerId(),
                        'ownerModalId'           => InputFieldsDevModalWidget::getModalWindowName(),
                        'returnUrl'              => \yii\helpers\Url::toRoute([
                            '/feedback/dev-input-fields/load-modal',
                            'inputFileTemplateId' => $widget->devInputFilesGroup->inputFileTemplate->id,
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
