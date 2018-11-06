<?php

/* @var $this \yii\web\View */
/* @var $translateModel \Iliich246\YicmsFeedback\InputFields\InputFieldNamesTranslatesForm */

?>

<?= $form->field($translateModel, "[$translateModel->key]devName")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]devDescription")->textarea() ?>

<?= $form->field($translateModel, "[$translateModel->key]adminName")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]adminDescription")->textarea() ?>
