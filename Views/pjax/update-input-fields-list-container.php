<?php

use yii\widgets\Pjax;
use Iliich246\YicmsCommon\Assets\FieldsDevAsset;
use Iliich246\YicmsCommon\Fields\FieldsDevModalWidget;
use Iliich246\YicmsFeedback\InputFields\InputFieldsDevModalWidget;

/** @var $this \yii\web\View */
/** @var $inputFieldTemplateReference string */
/** @var $inputFieldTemplates \Iliich246\YicmsFeedback\InputFields\InputFieldTemplate[] */

//FieldsDevAsset::register($this);
$js = <<<JS
;(function() {
    var addField = $('.add-input-field');

    var homeUrl = $(addField).data('homeUrl');

    var emptyModalUrl             = homeUrl + '/feedback/dev-input-fields/empty-modal';
    var loadModalUrl              = homeUrl + '/feedback/dev-input-fields/load-modal';
    var updateInputFieldsListUrl  = homeUrl + '/feedback/dev-input-fields/update-input-fields-list-container';
    var inputFieldTemplateUpUrl   = homeUrl + '/feedback/dev-input-fields/input-field-template-up-order';
    var inputFieldTemplateDownUrl = homeUrl + '/feedback/dev-input-fields/input-field-template-down-order';

    var redirectToUpdateNeedSecondPjaxRequest = false;

    var inputFieldTemplateReference = $(addField).data('inputFieldTemplateReference');
    var pjaxContainerName           = '#' + $(addField).data('pjaxContainerName');
    var pjaxFieldsModalName         = '#' + $(addField).data('fieldsModalName');
    var imageLoaderScr              = $(addField).data('loaderImageSrc');

    $(pjaxContainerName).on('pjax:send', function() {
        $(pjaxFieldsModalName)
            .find('.modal-content')
            .empty()
            .append('<img src="' + imageLoaderScr + '" style="text-align:center">');
    });

    $(pjaxContainerName).on('pjax:success', function(event) {

        var inputFieldTemplateHidden = $('#input-field-template-id-hidden');

        if ($(inputFieldTemplateHidden).val())
            $(addField).data('currentSelectedInputFieldTemplate', $(inputFieldTemplateHidden).val());

        var fieldForm = $('#create-update-input-fields');

        if ($(fieldForm).data('saveAndExit')) {
            $(pjaxFieldsModalName).modal('hide');

            $.pjax({
                url: updateInputFieldsListUrl + '?inputFieldTemplateReference=' + inputFieldTemplateReference,
                container: '#update-input-fields-list-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });

            return;
        }

        var redirectToUpdate           = $(fieldForm).data('redirectToUpdateInputField');
        var fieldTemplateIdForRedirect = $(fieldForm).data('inputFieldTemplateIdRedirect');

        if (redirectToUpdate) {
            $.pjax({
                url: updateInputFieldsListUrl + '?inputFieldTemplateReference=' + inputFieldTemplateReference,
                container: '#update-input-fields-list-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });

            redirectToUpdateNeedSecondPjaxRequest = fieldTemplateIdForRedirect;

            return;
        }

        var isValidatorResponse = !!($('.validator-response').length);

        if (isValidatorResponse) return loadModal($(addField).data('currentSelectedFieldTemplate'));

        if (!$(event.target).find('form').is('[data-yicms-saved]')) return false;

        $.pjax({
            url: updateInputFieldsListUrl + '?inputFieldTemplateReference=' + inputFieldTemplateReference,
            container: '#update-input-fields-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $('#update-input-fields-list-container').on('pjax:success', function(event) {
        if (redirectToUpdateNeedSecondPjaxRequest) {
            loadModal(redirectToUpdateNeedSecondPjaxRequest);
            redirectToUpdateNeedSecondPjaxRequest = false;
        }
    });

    $(document).on('click', '.input-field-item p', function(event) {
        var inputFieldTemplate = $(this).data('input-field-template-id');

        $(addField).data('currentSelectedInputFieldTemplate', inputFieldTemplate);

        loadModal(inputFieldTemplate);
    });

    $(addField).on('click', function() {
        $.pjax({
            url: emptyModalUrl + '?inputFieldTemplateReference=' + inputFieldTemplateReference ,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.field-arrow-up', function() {
        $.pjax({
            url: inputFieldTemplateUpUrl + '?inputFieldTemplateId=' + $(this).data('inputFieldTemplateId'),
            container: '#update-input-fields-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.field-arrow-down', function() {
        $.pjax({
            url: inputFieldTemplateDownUrl + '?inputFieldTemplateId=' + $(this).data('inputFieldTemplateId'),
            container: '#update-input-fields-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    function loadModal(inputFieldTemplate) {
        $.pjax({
            url: loadModalUrl + '?inputFieldTemplateId=' + inputFieldTemplate,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });

        $(pjaxFieldsModalName).modal('show');
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
            <h3>List of input fields</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-input-field"
                        data-toggle="modal"
                        data-target="#inputFieldsDevModal"
                        data-input-field-template-reference="<?= $inputFieldTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= InputFieldsDevModalWidget::getPjaxContainerId() ?>"
                        data-fields-modal-name="<?= InputFieldsDevModalWidget::getModalWindowName() ?>"
                        data-loader-image-src="<?= $src ?>"
                        data-current-selected-input-field-template="null"
                    >
                    <span class="glyphicon glyphicon-plus-sign"></span> Add new field
                </button>
            </div>
        </div>
        <?php if (isset($inputFieldTemplates)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-input-fields-list-container'
                ]
            ]) ?>

            <div class="list-block">
                <div class="row content-block-title">

                </div>
                <?php foreach ($inputFieldTemplates as $inputFieldTemplate): ?>
                    <div class="row list-items input-field-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-field-template="<?= $inputFieldTemplate->input_field_template_reference ?>"
                               data-input-field-template-id="<?= $inputFieldTemplate->id ?>"
                                >
                                <?= $inputFieldTemplate->program_name ?>
                            </p>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($inputFieldTemplate->visible): ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                            <?php if ($inputFieldTemplate->editable): ?>
                                <span class="glyphicon glyphicon-pencil"></span>
                            <?php endif; ?>
                            <?php if ($inputFieldTemplate->canUpOrder()): ?>
                                <span class="glyphicon field-arrow-up glyphicon-arrow-up"
                                      data-input-field-template-id="<?= $inputFieldTemplate->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputFieldTemplate->canDownOrder()): ?>
                                <span class="glyphicon field-arrow-down glyphicon-arrow-down"
                                      data-input-field-template-id="<?= $inputFieldTemplate->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
