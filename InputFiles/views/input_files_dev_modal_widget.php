<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;
use Iliich246\YicmsCommon\Validators\ValidatorsListWidget;
use Iliich246\YicmsFeedback\InputFiles\DevInputFilesGroup;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;

/** @var $this \yii\web\View */
/** @var $widget InputFilesDevModalWidget */
/** @var $bundle \Iliich246\YicmsCommon\Assets\DeveloperAsset  */

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);

$modalName = InputFilesDevModalWidget::getModalWindowName();
$deleteLink = $widget->deleteLink . '?inputFileBlockId=';

$js = <<<JS
;(function() {
    $(document).on('click', '#input-file-delete', function() {
        var button = ('#input-file-delete');

        if (!$(button).is('[data-input-file-block-id]')) return;

        var inputFileBlockId             = $(button).data('inputFileBlockId');
        var inputFileBlockHasConstraints = $(button).data('inputFileBlockHasConstraints');
        var pjaxContainer                = $('#update-input-files-list-container');

        if (!($(this).hasClass('input-file-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('input-file-confirm-state');
        } else {
            if (!inputFileBlockHasConstraints) {
                $.pjax({
                    url: '{$deleteLink}' + inputFileBlockId,
                    container: '#update-input-files-list-container',
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
                var deleteButtonRow = $('.delete-button-row-input-files');

                var template = _.template($('#delete-with-pass-template').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);

                var passwordInput = $('#input-file-delete-password-input');
                var buttonDelete  = $('#input-file-button-delete-with-pass');

                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: '{$deleteLink}' + inputFileBlockId + '&deletePass=' + $(passwordInput).val(),
                        container: '#update-input-files-list-container',
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
                             message: "Files block template has not deleted",
                             className: 'bootbox-error'
                         });
                    });
                });
            }
        }
    });
})();
JS;

$this->registerJs($js, $this::POS_READY);

$this->registerAssetBundle(\Iliich246\YicmsCommon\Assets\LodashAsset::className());

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
                'id' => 'input-file-block-id-hidden'
            ]) ?>
        <?php endif; ?>

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" id="myModalLabel">
                    <?php if ($widget->devInputFilesGroup->scenario == DevInputFilesGroup::SCENARIO_CREATE): ?>
                        Create new input file
                    <?php else: ?>
                        Update existed input file (<?= $widget->devInputFilesGroup->inputFilesBlock->program_name ?>)
                        <?= $widget->devInputFilesGroup->inputFilesBlock->id ?>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'program_name') ?>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'type')->dropDownList(
                            \Iliich246\YicmsFeedback\InputFiles\InputFilesBlock::getTypes()
                        ) ?>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'max_files') ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <br>
                        <p>zero value - infinite count of files in block</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12 ">
                        <?= $form->field($widget->devInputFilesGroup->inputFilesBlock, 'active')->checkbox() ?>
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
                    <div class="row delete-button-row-input-files">
                        <div class="col-xs-12">
                            <br>

                            <p>IMPORTANT! Do not delete input file blocks without serious reason!</p>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="input-file-delete"
                                    data-input-file-block-reference="
                                    <?= $widget->devInputFilesGroup->inputFilesBlock->input_file_template_reference ?>"
                                    data-input-file-block-id="<?= $widget->devInputFilesGroup->inputFilesBlock->id ?>"
                                    data-input-file-block-has-constraints="<?= (int)$widget->devInputFilesGroup->inputFilesBlock->isConstraints() ?>"
                            >
                                Delete input file
                            </button>
                        </div>
                    </div>
                    <script type="text/template" id="delete-with-pass-template">
                        <div class="col-xs-12">
                            <br>
                            <label for="input-file-delete-password-input">
                                Input file has constraints. Enter dev password for delete input file block
                            </label>
                            <input type="password"
                                   id="input-file-delete-password-input"
                                   class="form-control" name=""
                                   value=""
                                   aria-required="true"
                                   aria-invalid="false">
                            <br>
                            <button type="button"
                                    class="btn btn-danger"
                                    id="input-file-button-delete-with-pass"
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
                            '/feedback/dev-input-files/load-modal',
                            'inputFileBlockId' => $widget->devInputFilesGroup->inputFilesBlock->id,
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
