<?php

use yii\widgets\Pjax;
use Iliich246\YicmsFeedback\InputImages\InputImagesDevModalWidget;

/** @var $this \yii\web\View */
/** @var $inputImageTemplateReference string */
/** @var $inputImagesBlocks \Iliich246\YicmsFeedback\InputImages\InputImagesBlock[] */

$js = <<<JS
;(function() {
    var addInputImage = $('.add-input-image');

    var homeUrl = $(addInputImage).data('homeUrl');

    var emptyModalUrl            = homeUrl + '/feedback/dev-input-images/empty-modal';
    var loadModalUrl             = homeUrl + '/feedback/dev-input-images/load-modal';
    var updateInputImagesListUrl = homeUrl + '/feedback/dev-input-images/update-input-images-list-container';
    var inputImageBlockUpUrl     = homeUrl + '/feedback/dev-input-images/input-images-block-up-order';
    var inputImageBlockDownUrl   = homeUrl + '/feedback/dev-input-images/input-images-block-down-order';

    var redirectToUpdateNeedSecondPjaxRequest = false;

    var inputImageTemplateReference = $(addInputImage).data('inputImageTemplateReference');
    var pjaxContainerName           = '#' + $(addInputImage).data('pjaxContainerName');
    var pjaxImagesModalName         = '#' + $(addInputImage).data('imagesModalName');
    var imageLoaderScr              = $(addInputImage).data('loaderImageSrc');
    
    $(pjaxContainerName).on('pjax:send', function() {
        $(pjaxImagesModalName)
            .find('.modal-content')
            .empty()
            .append('<img src="' + imageLoaderScr + '" style="text-align:center">');
    });
    
    $(pjaxContainerName).on('pjax:success', function(event) {

        var inputImageBlockHidden = $('#input-image-block-id-hidden');

        if ($(inputImageBlockHidden).val())
            $(addInputImage).data('currentSelectedInputImageBlock', $(inputImageBlockHidden).val());

        var fileForm = $('#create-update-input-images');

        if ($(fileForm).data('saveAndExit')) {
            $(pjaxImagesModalName).modal('hide');

            $.pjax({
                url: updateInputImagesListUrl + '?inputImageTemplateReference=' + inputImageTemplateReference,
                container: '#update-input-images-list-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });

            return;
        }

        var redirectToUpdate           = $(fileForm).data('redirectToUpdateInputImage');
        var imageTemplateIdForRedirect = $(fileForm).data('inputImageBlockIdRedirect');

        if (redirectToUpdate) {
            $.pjax({
                url: updateInputImagesListUrl + '?inputImageTemplateReference=' + inputImageTemplateReference,
                container: '#update-input-images-list-container',
                scrollTo: false,
                push: false,
                type: "POST",
                timeout: 2500
            });

            redirectToUpdateNeedSecondPjaxRequest = imageTemplateIdForRedirect;

            return;
        }

        var isValidatorResponse = !!($('.validator-response').length);

        if (isValidatorResponse) return loadModal($(addInputImage).data('currentSelectedInputImageBlock'));

        if (!$(event.target).find('form').is('[data-yicms-saved]')) return false;

        $.pjax({
            url: updateInputImagesListUrl + '?inputImageTemplateReference=' + inputImageTemplateReference,
            container: '#update-input-images-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $('#update-input-images-list-container').on('pjax:success', function(event) {
        if (redirectToUpdateNeedSecondPjaxRequest) {
            loadModal(redirectToUpdateNeedSecondPjaxRequest);
            redirectToUpdateNeedSecondPjaxRequest = false;
        }
    });

    $(document).on('click', '.input-image-item p', function(event) {
        var inputImageBlock = $(this).data('input-image-block-id');

        $(addInputImage).data('currentSelectedInputImageBlock', inputImageBlock);

        loadModal(inputImageBlock);
    });

    $(addInputImage).on('click', function() {
        $.pjax({
            url: emptyModalUrl + '?inputImageTemplateReference=' + inputImageTemplateReference ,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.image-arrow-up', function() {
        $.pjax({
            url: inputImageBlockUpUrl + '?inputImageBlockId=' + $(this).data('inputImageBlockId'),
            container: '#update-input-images-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    $(document).on('click', '.image-arrow-down', function() {
        $.pjax({
            url: inputImageBlockDownUrl + '?inputImageBlockId=' + $(this).data('inputImageBlockId'),
            container: '#update-input-images-list-container',
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });
    });

    function loadModal(inputImageTemplate) {
        $.pjax({
            url: loadModalUrl + '?inputImageBlockId=' + inputImageTemplate,
            container: pjaxContainerName,
            scrollTo: false,
            push: false,
            type: "POST",
            timeout: 2500
        });

        $(pjaxImagesModalName).modal('show');
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
            <h3>List of input images</h3>
        </div>
        <div class="row control-buttons">
            <div class="col-xs-12">
                <button class="btn btn-primary add-input-image"
                        data-toggle="modal"
                        data-target="#inputImagesDevModal"
                        data-input-image-template-reference="<?= $inputImageTemplateReference ?>"
                        data-home-url="<?= \yii\helpers\Url::base() ?>"
                        data-pjax-container-name="<?= InputImagesDevModalWidget::getPjaxContainerId() ?>"
                        data-images-modal-name="<?= InputImagesDevModalWidget::getModalWindowName() ?>"
                        data-loader-image-src="<?= $src ?>"
                        data-current-selected-input-image-block="null"
                    >
                    <span class="glyphicon glyphicon-plus-sign"></span> Add new input image
                </button>
            </div>
        </div>

        <?php if (isset($inputImagesBlocks)): ?>
            <?php Pjax::begin([
                'options' => [
                    'id' => 'update-input-images-list-container'
                ],
                'enablePushState'    => false,
                'enableReplaceState' => false
            ]) ?>

            <div class="list-block">
                <div class="row content-block-title">

                </div>
                <?php foreach ($inputImagesBlocks as $inputImagesBlock): ?>
                    <div class="row list-items input-image-item">
                        <div class="col-xs-10 list-title">
                            <p data-input-image-block="<?= $inputImagesBlock->input_image_template_reference ?>"
                               data-input-image-block-id="<?= $inputImagesBlock->id ?>"
                                >
                                <?= $inputImagesBlock->program_name ?>
                            </p>
                        </div>
                        <div class="col-xs-2 list-controls">
                            <?php if ($inputImagesBlock->active): ?>
                                <span class="glyphicon glyphicon-eye-open"></span>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-eye-close"></span>
                            <?php endif; ?>
                            <?php if ($inputImagesBlock->editable): ?>
                                <span class="glyphicon glyphicon-pencil"></span>
                            <?php endif; ?>
                            <?php if ($inputImagesBlock->canUpOrder()): ?>
                                <span class="glyphicon image-arrow-up glyphicon-arrow-up"
                                      data-input-image-block-id="<?= $inputImagesBlock->id ?>"></span>
                            <?php endif; ?>
                            <?php if ($inputImagesBlock->canDownOrder()): ?>
                                <span class="glyphicon image-arrow-down glyphicon-arrow-down"
                                      data-input-image-block-id="<?= $inputImagesBlock->id ?>"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php Pjax::end() ?>
        <?php endif; ?>
    </div>
</div>
