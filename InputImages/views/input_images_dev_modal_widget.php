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
/** @var $bundle \Iliich246\YicmsCommon\Assets\DeveloperAsset */

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);

$modalName = InputImagesDevModalWidget::getModalWindowName();
$deleteLink = $widget->deleteLink . '?inputImageBlockId=';

$js = <<<JS
;(function() {
    $(document).on('click', '#input-image-delete', function() {
        var button = ('#input-image-delete');
        
        if (!$(button).is('[data-input-image-block-id]')) return;
        
        var inputImageBlockId             = $(button).data('inputImageBlockId');
        var inputImageBlockHasConstraints = $(button).data('inputImageBlockHasConstraints');
        var pjaxContainer                 = $('#update-input-images-list-container');
        
        if (!($(this).hasClass('input-image-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('input-image-confirm-state');
        } else {
            if (!inputImageBlockHasConstraints) {
                $.pjax({
                    url: '{$deleteLink}' + inputImageBlockId,
                    container: '#update-input-images-list-container',
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
                var deleteButtonRow = $('.delete-button-row-input-image');
                
                var template = _.template($('#delete-with-pass-template-input-image').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);
                
                var passwordInput = $('#input-images-block-delete-password-input');
                var buttonDelete  = $('#button-delete-with-pass-input-image');                
                
                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: '{$deleteLink}' + inputImageBlockId + '&deletePass=' + $(passwordInput).val(),
                        container: '#update-input-images-list-container',
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
                             message: "Images block template has not deleted",
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
                    <div class="row delete-button-row-input-image">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input images without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="input-image-delete"
                                    data-input-image-block-reference="<?= $widget->devInputImagesGroup->inputImagesBlock->input_image_template_reference ?>"
                                    data-input-image-block-id="<?= $widget->devInputImagesGroup->inputImagesBlock->id ?>"
                                    data-input-image-block-has-constraints="<?= (int)$widget->devInputImagesGroup->inputImagesBlock->isConstraints() ?>"
                            >
                                Delete input image block
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template-input-image">
                        <div class="col-xs-12">
                            <br>
                            <label for="input-images-block-delete-password-input">
                                Input image has constraints. Enter dev password for delete input image block
                            </label>
                            <input type="password"
                                   id="input-images-block-delete-password-input"
                                   class="form-control" name=""
                                   value=""
                                   aria-required="true"
                                   aria-invalid="false">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="button-delete-with-pass-input-image"
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
