<?php //template

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $inputImagesTemplates \Iliich246\YicmsFeedback\InputImages\InputImagesBlock[] */

$firstTime = true;
?>

<?php foreach($inputImagesTemplates as $inputImagesTemplate): ?>
    <?php if ($inputImagesTemplate->isEntities()): ?>
        <?php if ($firstTime): ?>
            <?php $firstTime = false; ?>
            <h3>Images:</h3>
        <?php endif; ?>
        <strong><?= $inputImagesTemplate->devName() ?>:</strong>
    <?php endif; ?>
    <?php foreach($inputImagesTemplate->getInputImages() as $inputImage): ?>
        <img src="<?= $inputImage->getSrc() ?>" alt="">
        <p><?= $inputImage->system_name ?></p>
    <?php endforeach; ?>
<?php endforeach; ?>
