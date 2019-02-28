<?php

/* @var $this \yii\web\View */
/* @var $translateModel \Iliich246\YicmsFeedback\InputImages\InputImageNamesTranslatesForm */

?>

<?= $form->field($translateModel, "[$translateModel->key]adminName")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]adminDescription")->textarea() ?>

<?= $form->field($translateModel, "[$translateModel->key]devName")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]devDescription")->textarea() ?>
