<?php //template

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $inputFilesTemplates \Iliich246\YicmsFeedback\InputFiles\InputFilesBlock[] */

$firstTime = true;
?>

<?php foreach($inputFilesTemplates as $inputFilesTemplate): ?>
    <?php if ($inputFilesTemplate->isEntities()): ?>
        <?php if ($firstTime): ?>
            <?php $firstTime = false; ?>
            <h3>Files:</h3>
        <?php endif; ?>
        <strong><?= $inputFilesTemplate->devName() ?>:</strong>
    <?php endif; ?>

    <?php foreach($inputFilesTemplate->getInputFiles() as $inputFile): ?>
        <a href="<?= $inputFile->uploadUrl() ?>"><?= $inputFile->getFileName() ?></a>
    <?php endforeach; ?>
<?php endforeach; ?>
