<?php

/** @var $this \yii\web\View */
/** @var $translateModel \Iliich246\YicmsFeedback\InputConditions\InputConditionValueNamesForm */

?>

<?= $form->field($translateModel, "[$translateModel->key]valueName")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]valueDescription")->textarea() ?>
