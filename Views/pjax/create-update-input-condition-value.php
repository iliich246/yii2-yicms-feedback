<?php

use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;
use Iliich246\YicmsFeedback\InputConditions\InputConditionValues;
use Iliich246\YicmsCommon\Widgets\SimpleTabsTranslatesWidget;

/** @var $this \yii\web\View */
/** @var $inputConditionTemplate \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate */
/** @var $inputConditionValue \Iliich246\YicmsFeedback\InputConditions\InputConditionValues */
/** @var $inputConditionValuesTranslates \Iliich246\YicmsFeedback\InputConditions\InputConditionValueNamesForm[] */

$js = <<<JS
;(function() {
    var conditionValueModal = $('.condition-create-update-input-value-modal');
    var deleteButton        = $('#condition-input-value-delete');
    var backButton          = $('.input-condition-create-update-value-back');
    
    var pjaxContainer   = $(conditionValueModal).parent('.pjax-container');
    var pjaxContainerId = '#' + $(pjaxContainer).attr('id');

    var homeUrl           = $(conditionValueModal).data('homeUrl');
    var returnUrl         = $(pjaxContainer).data('returnUrlInputConditionsValue');

    console.log(returnUrl);
    var redirectUpdateUrl = $(conditionValueModal).data('redirectUpdateUrl');
    var deleteUrl         = homeUrl + '/feedback/dev-input-conditions/delete-input-condition-value';
        
    var isReturn         = $(conditionValueModal).data('returnBack');
    var isRedirectUpdate = $(conditionValueModal).data('redirectUpdate');   

    if (isReturn) return goBack();

    if (isRedirectUpdate) return redirectUpdate(); 
            
    $(backButton).on('click', function(){
        goBack();
    });    
            
    function goBack() {
        $.pjax({
            url: returnUrl,
            container: pjaxContainerId,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500,
        });
    }  
              
    function redirectUpdate() {
        $.pjax({
            url: redirectUpdateUrl,
            container: pjaxContainerId,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500,
        });
    }  
                
$(deleteButton).on('click',  function() {

        var button = ('#condition-input-value-delete');

        if (!$(button).is('[data-input-condition-value-id]')) return;

        var inputConditionValueId        = $(button).data('inputConditionValueId');
        var inputConditionHasConstraints = $(button).data('inputConditionValueHasConstraints');

        if (!($(this).hasClass('input-condition-value-confirm-state'))) {
            $(this).before('<span>Are you sure? </span>');
            $(this).text('Yes, I`am sure!');
            $(this).addClass('input-condition-value-confirm-state');
        } else {
            if (!inputConditionHasConstraints) {
                $.pjax({
                    url: deleteUrl + '?inputConditionValueId=' + inputConditionValueId,
                    container: pjaxContainerId,
                    scrollTo: false,
                    push: false,
                    type: "POST",
                    timeout: 2500
                });

                var deleteActive = true;

                $(pjaxContainer).on('pjax:success', function(event) {

                    if (!deleteActive) return false;

                    deleteActive = false;
                });
            } else {
                var deleteButtonRow = $('.delete-button-row');

                var template = _.template($('#delete-with-pass-template').html());
                $(deleteButtonRow).empty();
                $(deleteButtonRow).append(template);

                var passwordInput = $('#condition-value-delete-password-input');
                var buttonDelete  = $('#button-delete-with-pass');

                $(buttonDelete).on('click', function() {
                    $.pjax({
                        url: deleteUrl + '?inputConditionValueId=' + inputConditionValueId +
                                         '&deletePass=' + $(passwordInput).val(),
                        container: pjaxContainerId,
                        scrollTo: false,
                        push: false,
                        type: "POST",
                        timeout: 2500
                    });

                    var deleteActive = true;

                    //$(pjaxContainer).on('pjax:success', function(event) {
                    //
                    //    if (!deleteActive) return false;
                    //
                    //    $('#conditionsDevModal').modal('hide');
                    //    deleteActive = false;
                    //});

                    $(pjaxContainer).on('pjax:error', function(event) {

                        $('#inputConditionsDevModal').modal('hide');

                        bootbox.alert({
                            size: 'large',
                            title: "Wrong dev password",
                            message: "Condition value has not deleted",
                            className: 'bootbox-error'
                        });
                    });
                });

//                $('#{modalName}').on('hide.bs.modal', function() {
//                    $(pjaxContainer).off('pjax:error');
//                    $(pjaxContainer).off('pjax:success');
//                    $('#{modalName}').off('hide.bs.modal');
//                });
            }
        }
    });                
})();
JS;

$this->registerJs($js);

if (isset($returnBack) && $returnBack) $return = 'true';
else $return = 'false';

if (isset($redirectUpdate)) $redirect = 'true';
else $redirect = 'false';

$inputConditionValue->isNewRecord ? $inputConditionValueId = '0' : $inputConditionValueId = $inputConditionValue->id;

?>

<div class="modal-content condition-create-update-input-value-modal"
     data-home-url="<?= \yii\helpers\Url::base() ?>"
     data-return-back="<?= $return ?>"
     data-redirect-update="<?= $redirect ?>"
     data-return-url="<?= \yii\helpers\Url::toRoute([
         '/feedback/dev-input-conditions/condition-input-values-list',
         'inputConditionTemplateId' => $inputConditionTemplate->id,
     ]) ?>"
     data-redirect-update-url="<?= \yii\helpers\Url::toRoute([
         '/feedback/dev-input-conditions/update-input-condition-value',
         'inputConditionValueId' => $inputConditionValueId,
     ]) ?>"
    >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 class="modal-title">
            <?php if ($inputConditionValue->scenario == InputConditionValues::SCENARIO_CREATE): ?>
                Create input condition value
            <?php else: ?>
                Update input condition value
            <?php endif; ?>
            <span class="glyphicon glyphicon-arrow-left input-condition-create-update-value-back"
                  style="float: right;margin-right: 20px"></span>
        </h3>
    </div>
    <?php $form = ActiveForm::begin([
        'id' => 'condition-create-update-input-value-form',
        'options' => [
            'data-pjax'        => true,
            'data-return-back' => $return
        ],
    ]);
    ?>
    <div class="modal-body">
        <div class="row">
            <div class="col-sm-6 col-xs-12">
                <?= $form->field($inputConditionValue, 'value_name') ?>
            </div>
            <div class="col-sm-6 col-xs-12">
                <br>
                <?= $form->field($inputConditionValue, 'is_default')->checkbox() ?>
            </div>
        </div>

        <?= SimpleTabsTranslatesWidget::widget([
            'form'            => $form,
            'translateModels' => $inputConditionValuesTranslates,
        ])
        ?>

        <?php if ($inputConditionValue->scenario == InputConditionValues::SCENARIO_UPDATE): ?>
            <div class="row delete-button-row">
                <div class="col-xs-12">
                    <br>
                    <button type="button"
                            class="btn btn-danger"
                            data-condition-input-value-id="<?= $inputConditionValue->id ?>"
                            data-condition-input-value-has-constraints="<?= (int)$inputConditionValue->isConstraints() ?>"
                            id="condition-value-delete">
                        Delete input condition value
                    </button>
                </div>
            </div>
            <script type="text/template" id="delete-with-pass-template">
                <div class="col-xs-12">
                    <br>
                    <label for="condition-value-delete-password-input">
                        Input condition value has constraints. Enter dev password for delete input condition value
                    </label>
                    <input type="password"
                           id="condition-value-delete-password-input"
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
        <?php endif; ?>

    </div>
    <div class="modal-footer">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('Save and back',
            ['class' => 'btn btn-success',
                'value' => 'true', 'name' => '_saveAndBack']) ?>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
    <?php ActiveForm::end(); ?>
</div>
