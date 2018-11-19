<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;
use Iliich246\YicmsCommon\Validators\ValidatorsListWidget;
use Iliich246\YicmsFeedback\InputImages\InputImagesDevModalWidget;
use Iliich246\YicmsFeedback\InputImages\DevInputImagesGroup;

/** @var $this \yii\web\View */
/** @var $widget InputImagesDevModalWidget */

if ($widget->devInputImagesGroup->scenario == DevInputImagesGroup::SCENARIO_CREATE &&
    $widget->devInputImagesGroup->justSaved)
    $redirectToUpdate = 'true';
else
    $redirectToUpdate = 'false';

if ($redirectToUpdate == 'true')
    $inputImagesBlockIdForRedirect = $widget->devInputImagesGroup->inputImagesBlock->id;
else
    $inputImagesBlockIdForRedirect = '0';

?>

<div class="modal fade"
     id="<?= InputImagesDevModalWidget::getModalWindowName() ?>"
     tabindex="-1"
     role="dialog"
     data-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <?php Pjax::begin([
            'options' => [
                'id'                         => InputImagesDevModalWidget::getPjaxContainerId(),
                'class'                      => 'pjax-container',
                'data-return-url'            => '0',
                'data-return-url-validators' => '0',
            ],
        ]); ?>
        <?php $form = ActiveForm::begin([
            'id'      => InputImagesDevModalWidget::getFormName(),
            'action'  => $widget->action,
            'options' => [
                'data-pjax'                           => true,
                'data-yicms-saved'                    => $widget->dataSaved,
                'data-save-and-exit'                  => $widget->saveAndExit,
                'data-redirect-to-update-input-image' => $redirectToUpdate,
                'data-input-image-block-id-redirect'  => $inputImagesBlockIdForRedirect
            ],
        ]);
        ?>

        <?php if ($widget->devInputImagesGroup->scenario == DevInputImagesGroup::SCENARIO_UPDATE): ?>
            <?= Html::hiddenInput('_inputImageBlockId', $widget->devInputImagesGroup->inputImagesBlock->id, [
                'id' => 'input-image-block-id-hidden'
            ]) ?>
        <?php endif; ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" id="myModalLabel">
                    <?php if ($widget->devInputImagesGroup->scenario == DevInputImagesGroup::SCENARIO_CREATE): ?>
                        Create new input image
                    <?php else: ?>
                        Update input image (<?= $widget->devInputImagesGroup->inputImagesBlock->program_name ?>)
                        <?= $widget->devInputImagesGroup->inputImagesBlock->id ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?= $form->field($widget->devInputImagesGroup->inputImagesBlock, 'program_name') ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?= $form->field($widget->devInputImagesGroup->inputImagesBlock, 'max_images') ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputImagesGroup->inputImagesBlock, 'visible')->checkbox() ?>
                    </div>
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputImagesGroup->inputImagesBlock, 'editable')->checkbox() ?>
                    </div>
                </div>

                <?= SimpleTabsTranslatesWidget::widget([
                    'form'            => $form,
                    'translateModels' => $widget->devInputImagesGroup->inputImagesNameTranslates,
                ])
                ?>

                <?php if ($widget->devInputImagesGroup->scenario == DevInputImagesGroup::SCENARIO_UPDATE): ?>
                    <div class="row delete-button-row-field">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input images without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="field-delete"
                                    data-input-image-block-reference="<?= $widget->devInputImagesGroup->inputImagesBlock->input_image_template_reference ?>"
                                    data-input-image-block-id="<?= $widget->devInputImagesGroup->inputImagesBlock->id ?>"
                                    data-input-image-has-constraints="<?= (int)$widget->devInputImagesGroup->inputImagesBlock->isConstraints() ?>"
                            >
                                Delete input field
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template">
                        <div class="col-xs-12">
                            <br>
                            <label for="field-delete-password-input">
                                Input image has constraints. Enter dev password for delete input image block
                            </label>
                            <input type="password"
                                   id="image-delete-password-input"
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
                        'validatorReference'     => $widget->devInputImagesGroup->inputImagesBlock,
                        'ownerPjaxContainerName' => InputImagesDevModalWidget::getPjaxContainerId(),
                        'ownerModalId'           => InputImagesDevModalWidget::getModalWindowName(),
                        'returnUrl'              => \yii\helpers\Url::toRoute([
                            '/feedback/dev-input-images/load-modal',
                            'inputImagesBlockId' => $widget->devInputImagesGroup->inputImagesBlock->id,
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
