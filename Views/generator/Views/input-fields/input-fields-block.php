<?php //template

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $inputFieldTemplates \Iliich246\YicmsFeedback\InputFields\InputFieldTemplate[] */

?>

<?php foreach ($inputFieldTemplates as $inputFieldTemplate): ?>
    <?php $inputField = $feedback->getInputField($inputFieldTemplate->program_name) ?>
    <?php if (!$inputField->isNonexistent()): ?>
        <p>
            <strong><?= $inputField->devName() ?>: </strong>
            <?= $inputField ?>
        </p>
    <?php endif; ?>
<?php endforeach; ?>


