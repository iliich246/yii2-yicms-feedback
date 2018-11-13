<?php

use yii\widgets\Pjax;
use Iliich246\YicmsFeedback\InputFiles\InputFilesDevModalWidget;

/* @var $this \yii\web\View */
/* @var $inputFileTemplateReference string */
/* @var $inputFilesBlocks \Iliich246\YicmsFeedback\InputFiles\InputFilesBlock[] */

$js = <<<JS
;(function() {
    var addInputFile = $('.add-input-file');

    var homeUrl = $(addInputFile).data('homeUrl');

    var emptyModalUrl           = homeUrl + '/feedback/dev-input-files/empty-modal';
    var loadModalUrl            = homeUrl + '/feedback/dev-input-files/load-modal';
    var updateInputFilesListUrl = homeUrl + '/feedback/dev-input-files/update-input-files-list-container';
    var inputFileBlockUpUrl     = homeUrl + '/feedback/dev-input-files/input-files-block-up-order';
    var inputFileBlockDownUrl   = homeUrl + '/feedback/dev-input-files/input-files-block-down-order';

    var redirectToUpdateNeedSecondPjaxRequest = false;

    var inputFileTemplateReference = $(addInputFile).data('inputFileTemplateReference');
    var pjaxContainerName          = '#' + $(addInputFile).data('pjaxContainerName');
    var pjaxFilesModalName         = '#' + $(addInputFile).data('filesModalName');
    var imageLoaderScr             = $(addInputFile).data('loaderImageSrc');

    $(pjaxContainerName).on('pjax:send', function() {
        $(pjaxFilesModalName)
            .find('.modal-content')
            .empty()
            .append('<img src="' + imageLoaderScr + '" style="text-align:center">');
    });
    /*
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
    */

    $('#update-input-files-list-container').on('pjax:success', function(event) {
        if (redirectToUpdateNeedSecondPjaxRequest) {
            loadModal(redirectToUpdateNeedSecondPjaxRequest);
            redirectToUpdateNeedSecondPjaxRequest = false;
        }
    });

    $(document).on('click', '.input-file-item p', function(event) {
        var inputFileBlock = $(this).data('input-file-block-id');

        $(addInputFile).data('currentSelectedInputFileTemplate', inputFieldTemplate);

        loadModal(inputFieldTemplate);
    });

    $(addInputFile).on('click', function() {
        $.pjax({
            url: emptyModalUrl + '?inputFileTemplateReference=' + inputFileTemplateReference ,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.input-file-item p', function(event) {
        var inputFileTemplate = $(this).data('input-file-template-id');

        $(addInputFile).data('currentSelectedInputFileTemplate', inputFileTemplate);

        loadModal(inputFileTemplate);
    });

    $(document).on('click', '.file-arrow-up', function() {
        $.pjax({
            url: inputFileBlockUpUrl + '?inputFileBlockId=' + $(this).data('inputFileBlockId'),
            container: '#update-input-files-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.file-arrow-down', function() {
        $.pjax({
            url: inputFileBlockDownUrl + '?inputFileBlockId=' + $(this).data('inputFileBlockId'),
            container: '#update-input-files-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    function loadModal(inputFileTemplate) {
        $.pjax({
            url: loadModalUrl + '?inputFileTemplateId=' + inputFileTemplate,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });

        $(pjaxFilesModalName).modal('show');
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
            <h3>List of input files</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-input-file"
                        data-toggle="modal"
                        data-target="#<?= InputFilesDevModalWidget::getPjaxContainerId() ?>"
                        data-input-file-template-reference="<?= $inputFileTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= InputFilesDevModalWidget::getPjaxContainerId() ?>"
                        data-files-modal-name="<?= InputFilesDevModalWidget::getModalWindowName() ?>"
                        data-loader-image-src="<?= $src ?>"
                        data-current-selected-input-file-template="null"
                >
                    <span class="glyphicon glyphicon-plus-sign"></span> Add new input file
                </button>
            </div>
        </div>
        <?php if (isset($inputFilesBlocks)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-input-files-list-container'
                ]
            ]) ?>

            <div class="list-block">
                <div class="row content-block-title">

                </div>
                <?php foreach ($inputFilesBlocks as $inputFilesBlock): ?>
                    <div class="row list-items input-file-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-file-template="<?= $inputFilesBlock->input_file_template_reference ?>"
                               data-input-file-template-id="<?= $inputFilesBlock->id ?>"
                            >
                                <?= $inputFilesBlock->program_name ?>
                            </p>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($inputFilesBlock->visible): ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->editable): ?>
                                <span class="glyphicon glyphicon-pencil"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->canUpOrder()): ?>
                                <span class="glyphicon field-arrow-up glyphicon-arrow-up"
                                      data-input-file-block-id="<?= $inputFilesBlock->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputFilesBlock->canDownOrder()): ?>
                                <span class="glyphicon field-arrow-down glyphicon-arrow-down"
                                      data-input-file-block-id="<?= $inputFilesBlock->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
