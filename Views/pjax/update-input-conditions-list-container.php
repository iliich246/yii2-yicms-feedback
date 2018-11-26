<?php

use yii\widgets\Pjax;
use Iliich246\YicmsFeedback\InputConditions\InputConditionsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $inputConditionTemplateReference string */
/** @var $inputConditionTemplates \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate[] */

$js = <<<JS
;(function() {
    var addInputCondition = $('.add-input-condition');

    var homeUrl = $(addInputCondition).data('homeUrl');

    var emptyModalUrl                   = homeUrl + '/feedback/dev-input-conditions/empty-modal';
    var loadModalUrl                    = homeUrl + '/feedback/dev-input-conditions/load-modal';
    var updateInputConditionsListUrl    = homeUrl + '/feedback/dev-input-conditions/update-input-conditions-list-container';
    var inputConditionTemplateUpUrl     = homeUrl + '/feedback/dev-input-conditions/input-conditions-template-up-order';
    var inputConditionTemplateDownUrl   = homeUrl + '/feedback/dev-input-conditions/input-conditions-template-down-order';
    var inputConditionDataList          = homeUrl + '/feedback/dev-input-conditions/input-condition-values-list';

    var inputConditionTemplateReference = $(addInputCondition).data('inputConditionTemplateReference');
    var pjaxContainerName               = '#' + $(addInputCondition).data('pjaxContainerName');
    var pjaxConditionsModalName         = '#' + $(addInputCondition).data('conditionsModalName');
    var imageLoaderScr                  = $(addInputCondition).data('loaderImageSrc');

    var redirectToUpdateNeedSecondPjaxRequest = false;

    $(pjaxContainerName).on('pjax:send', function() {
        $(pjaxConditionsModalName)
            .find('.modal-content')
            .empty()
            .append('<img src="' + imageLoaderScr + '" style="text-align:center">');
    });
    
    $(pjaxContainerName).on('pjax:success', function(event) {

        var inputConditionBlockHidden = $('#input-condition-template-id-hidden');

        if ($(inputConditionBlockHidden).val())
            $(addInputCondition).data('currentSelectedInputConditionTemplate', $(inputConditionBlockHidden).val());

        var fileForm = $('#create-update-input-conditions');

        if ($(fileForm).data('saveAndExit')) {
            $(pjaxConditionsModalName).modal('hide');

            $.pjax({
                url: updateInputConditionsListUrl + '?inputConditionTemplateReference=' + inputConditionTemplateReference,
                container: '#update-input-conditions-list-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });

            return;
        }

        var redirectToUpdate               = $(fileForm).data('redirectToUpdateInputCondition');
        var conditionTemplateIdForRedirect = $(fileForm).data('inputConditionTemplateIdRedirect');

        if (redirectToUpdate) {
            $.pjax({
                url: updateInputConditionsListUrl + '?inputConditionTemplateReference=' + inputConditionTemplateReference,
                container: '#update-input-conditions-list-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });

            redirectToUpdateNeedSecondPjaxRequest = conditionTemplateIdForRedirect;

            return;
        }

        var isValidatorResponse = !!($('.validator-response').length);

        if (isValidatorResponse) return loadModal($(addInputCondition).data('currentSelectedInputFileBlock'));

        if (!$(event.target).find('form').is('[data-yicms-saved]')) return false;

        $.pjax({
            url: updateInputConditionsListUrl + '?inputConditionTemplateReference=' + inputConditionTemplateReference,
            container: '#update-input-conditions-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $('#update-input-conditions-list-container').on('pjax:success', function(event) {
        if (redirectToUpdateNeedSecondPjaxRequest) {
            loadModal(redirectToUpdateNeedSecondPjaxRequest);
            redirectToUpdateNeedSecondPjaxRequest = false;
        }
    });

    $(document).on('click', '.input-condition-item p', function(event) {
        var inputConditionTemplate = $(this).data('input-condition-template-id');

        $(addInputCondition).data('currentSelectedInputConditionTemplate', inputConditionTemplate);

        loadModal(inputConditionTemplate);
    });

    $(addInputCondition).on('click', function() {
        $.pjax({
            url: emptyModalUrl + '?inputConditionTemplateReference=' + inputConditionTemplateReference ,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });
    
    $(document).on('click', '.condition-arrow-up', function() {
        $.pjax({
            url: inputConditionTemplateUpUrl + '?inputConditionTemplateId=' + $(this).data('inputConditionTemplateId'),
            container: '#update-input-conditions-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.condition-arrow-down', function() {
        $.pjax({
            url: inputConditionTemplateDownUrl + '?inputConditionTemplateId=' + $(this).data('inputConditionTemplateId'),
            container: '#update-input-conditions-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.input-condition-data-list', function() {
        var inputConditionTemplateId = $(this).data('inputConditionTemplateId');

        $('#input-conditions-pjax-container').data('returnUrlConditionsList', $(this).data('returnUrlConditionsList'));

        $.pjax({
            url: inputConditionDataList + '?inputConditionTemplateId=' + $(this).data('inputConditionTemplateId'),
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    function loadModal(inputConditionTemplate) {
        $.pjax({
            url: loadModalUrl + '?inputConditionTemplateId=' + inputConditionTemplate,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });

        $(pjaxConditionsModalName).modal('show');
    }
})();
JS;

$bundle = \Iliich246\YicmsCommon\Assets\DeveloperAsset::register($this);
$src = $bundle->baseUrl . '/loader.svg';

$this->registerJs($js, $this::POS_READY);

?>

<div class="row content-block form-block">
    <div class="col-xs-12">
        <div class="content-block-title">
            <h3>List of input conditions</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-input-condition"
                        data-toggle="modal"
                        data-target="#inputConditionsDevModal"
                        data-input-condition-template-reference="<?= $inputConditionTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= InputConditionsDevModalWidget::getPjaxContainerId() ?>"
                        data-conditions-modal-name="<?= InputConditionsDevModalWidget::getModalWindowName() ?>"
                        data-loader-image-src="<?= $src ?>"
                        data-current-selected-input-condition-template="null"
                    >
                    <span class="glyphicon glyphicon-plus-sign"></span> Add new input condition
                </button>
            </div>
        </div>
        <?php if (isset($inputConditionTemplates)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-input-conditions-list-container'
                ]
            ]) ?>

            <div class="list-block">
                <div class="row content-block-title">

                </div>
                <?php foreach ($inputConditionTemplates as $inputConditionsTemplate): ?>
                    <div class="row list-items input-condition-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-condition-template="<?= $inputConditionsTemplate->input_condition_template_reference ?>"
                               data-input-condition-template-id="<?= $inputConditionsTemplate->id ?>"
                                >
                                <?= $inputConditionsTemplate->program_name ?>
                            </p>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($inputConditionsTemplate->visible): ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                            <?php if ($inputConditionsTemplate->editable): ?>
                                <span class="glyphicon glyphicon-pencil"></span>
                            <?php endif; ?>
                            <?php if ($inputConditionsTemplate->canUpOrder()): ?>
                                <span class="glyphicon condition-arrow-up glyphicon-arrow-up"
                                      data-input-condition-template-id="<?= $inputConditionsTemplate->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputConditionsTemplate->canDownOrder()): ?>
                                <span class="glyphicon condition-arrow-down glyphicon-arrow-down"
                                      data-input-condition-template-id="<?= $inputConditionsTemplate->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
