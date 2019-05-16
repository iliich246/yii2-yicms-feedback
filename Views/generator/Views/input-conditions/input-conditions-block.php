<?php //template

/** @var $this \yii\web\View */
/** @var $feedback \Iliich246\YicmsFeedback\Base\Feedback */
/** @var $inputConditionsTemplates \Iliich246\YicmsFeedback\InputConditions\InputConditionTemplate[] */

?>

<?php foreach($inputConditionsTemplates as $inputConditionsTemplate): ?>
    <?php $inputCondition = $feedback->getInputCondition($inputConditionsTemplate->program_name) ?>
    <?php if (!$inputCondition->isNonexistent()): ?>
        <p>
            <strong><?= $inputCondition->devName() ?>: </strong>
            <?= $inputCondition ?>
        </p>
    <?php endif; ?>
<?php endforeach; ?>
