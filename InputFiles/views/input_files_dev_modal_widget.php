<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;
use Iliich246\YicmsCommon\Validators\ValidatorsListWidget;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;
use Iliich246\YicmsFeedback\InputFiles\DevInputFilesGroup;

/** @var $this \yii\web\View */
/** @var $widget InputFilesDevModalWidget */

if ($widget->devInputFilesGroup->scenario == DevInputFilesGroup::SCENARIO_CREATE &&
    $widget->devInputFilesGroup->justSaved)
    $redirectToUpdate = 'true';
else
    $redirectToUpdate = 'false';

if ($redirectToUpdate == 'true')
    $inputFilesBlockIdForRedirect = $widget->devInputFilesGroup->inputFilesBlock->id;
else
    $inputFilesBlockIdForRedirect = '0';

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
                'data-pjax'                            => true,
                'data-yicms-saved'                     => $widget->dataSaved,
                'data-save-and-exit'                   => $widget->saveAndExit,
                'data-redirect-to-update-input-file'   => $redirectToUpdate,
                'data-input-file-template-id-redirect' => $inputFilesBlockIdForRedirect
            ],
        ]);
        ?>

        <?php if ($widget->devInputFilesGroup->scenario == DevInputFilesGroup::SCENARIO_UPDATE): ?>
            <?= Html::hiddenInput('_inputFilesBlockId', $widget->devInputFilesGroup->inputFilesBlock->id, [
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
                        Update existed field (<?= $widget->devInputFilesGroup->inputFilesBlock->program_name ?>)
                        <?= $widget->devInputFilesGroup->inputFilesBlock->id ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'program_name') ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'visible')->checkbox() ?>
                    </div>
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'editable')->checkbox() ?>
                    </div>
                </div>

                <?= SimpleTabsTranslatesWidget::widget([
                    'form'            => $form,
                    'translateModels' => $widget->devInputFilesGroup->inputFilesNameTranslates,
                ])
                ?>

                <?php if ($widget->devInputFilesGroup->scenario == DevInputFilesGroup::SCENARIO_UPDATE): ?>
                    <div class="row delete-button-row-field">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input fields without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="field-delete"
                                    data-input-field-template-reference="<?= $widget->devInputFilesGroup->inputFilesBlock->input_field_template_reference ?>"
                                    data-input-field-template-id="<?= $widget->devInputFilesGroup->inputFilesBlock->id ?>"
                                    data-input-field-has-constraints="<?= (int)$widget->devInputFilesGroup->inputFilesBlock->isConstraints() ?>"
                            >
                                Delete input field
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template">
                        <div class="col-xs-12">
                            <br>
                            <label for="field-delete-password-input">
                                Input file has constraints. Enter dev password for delete input file block
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
                        'validatorReference'     => $widget->devInputFilesGroup->inputFilesBlock,
                        'ownerPjaxContainerName' => InputFilesDevModalWidget::getPjaxContainerId(),
                        'ownerModalId'           => InputFilesDevModalWidget::getModalWindowName(),
                        'returnUrl'              => \yii\helpers\Url::toRoute([
                            '/feedback/dev-input-fields/load-modal',
                            'inputFilesBlockId' => $widget->devInputFilesGroup->inputFilesBlock->id,
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
