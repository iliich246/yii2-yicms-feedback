<?php

/** @var $this \yii\web\View */
/** @var $inputConditionTemplate \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate */
/** @var $inputConditionValues  \Iliich246\YicmsFeedback\InputConditions\InputConditionValues[] */

$js = <<<JS
;(function() {
    var inputConditionDataListModal = $('.input-condition-values-list-modal');

    var homeUrl = $(inputConditionDataListModal).data('homeUrl');

    var createInputConditionValueUrl        = homeUrl + '/feedback/dev-input-conditions/create-input-condition-value';
    var updateConditionValueUrl             = homeUrl + '/feedback/dev-input-conditions/update-input-condition-value';
    var conditionValueUpUrl                 = homeUrl + '/feedback/dev-input-conditions/input-condition-value-up-order';
    var conditionValueDownUrl               = homeUrl + '/feedback/dev-input-conditions/input-condition-value-down-order';

    var pjaxContainer   = $(inputConditionDataListModal).parent('.pjax-container');
    var pjaxContainerId = '#' + $(pjaxContainer).attr('id');

    var returnUrl       = $(pjaxContainer).data('returnUrlConditionsList');

    var backButton        = $('.input-condition-values-list-back');
    var addNewInputValueButton = $('.add-new-input-condition-value-button');

    $(backButton).on('click', goBack);

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

    $(addNewInputValueButton).on('click', function() {
        $(pjaxContainer).data('returnUrlInputConditionsValue', $(this).data('returnUrlInputConditionsValue'));

        $.pjax({
            url: createInputConditionValueUrl + '?inputConditionTemplateId=' + $(this).data('inputConditionTemplateId'),
            container: pjaxContainerId,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500,
        });
    });

    $('.input-condition-value-arrow-up-modal').on('click', function() {
        $.pjax({
            url: conditionValueUpUrl
                 + '?inputConditionValueId=' + $(this).data('inputConditionValueId'),
            container: pjaxContainerId,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $('.input-condition-value-block-item').on('click', function() {
        $(pjaxContainer).data('returnUrlInputConditionsValue', $('.add-new-input-condition-value-button').data('returnUrlInputConditionsValue'));

        $.pjax({
            url: updateConditionValueUrl
                 + '?inputConditionValueId=' + $(this).data('inputConditionValueId'),
            container: pjaxContainerId,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });
})();
JS;

$this->registerJs($js);

?>

<div class="modal-content input-condition-values-list-modal"
     data-home-url="<?= \yii\helpers\Url::base() ?>"
     data-input-condition-template-reference="<?= $inputConditionTemplate->input_condition_template_reference ?>"
     data-return-url-fields="<?= \yii\helpers\Url::toRoute([
         '/common/dev-fields/update-fields-list-container-dependent',
         'conditionTemplateReference' => $inputConditionTemplate->input_condition_template_reference,
     ]) ?>"
    >
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 class="modal-title">
            Input conditions data list
            <span class="glyphicon glyphicon-arrow-left input-condition-values-list-back"
                  style="float: right;margin-right: 20px"></span>
        </h3>
        <?php if ($inputConditionTemplate->type == \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate::TYPE_CHECKBOX): ?>
            <h4>For "checkbox" condition type all this values will be ignored</h4>
        <?php endif; ?>
    </div>
    <div class="modal-body">
        <button class="btn btn-primary add-new-input-condition-value-button"
                data-input-condition-template-id="<?= $inputConditionTemplate->id ?>"
                data-return-url-input-conditions-value="<?= \yii\helpers\Url::toRoute([
                    '/feedback/dev-input-conditions/input-condition-values-list',
                    'inputConditionTemplateId' => $inputConditionTemplate->id,
                ]) ?>"
            >
            Add new input condition data
        </button>
        <hr>
        <?php foreach($inputConditionValues as $inputConditionValue): ?>
            <div class="row list-items">
                <div class="col-xs-9 list-title">
                    <p data-input-condition-value-id="<?= $inputConditionValue->id ?>"
                       class="input-condition-value-block-item">
                        <?= $inputConditionValue->value_name ?>
                    </p>
                </div>
                <div class="col-xs-3 list-controls">
                    <?php if ($inputConditionValue->is_default): ?>
                        <span class="glyphicon glyphicon-tower"></span>
                    <?php endif; ?>
                    <?php if ($inputConditionValue->canUpOrder()): ?>
                        <span class="glyphicon input-condition-value-arrow-up-modal glyphicon-arrow-up"
                              data-input-condition-value-id="<?= $inputConditionValue->id ?>"></span>
                    <?php endif; ?>
                    <?php if ($inputConditionValue->canDownOrder()): ?>
                        <span class="glyphicon input-condition-value-arrow-down-modal glyphicon-arrow-down"
                              data-input-condition-value-id="<?= $inputConditionValue->id ?>"></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</div>
