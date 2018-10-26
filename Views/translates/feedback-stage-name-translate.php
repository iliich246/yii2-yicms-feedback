<?php

/* @var $this \yii\web\View */
/* @var $translateModel \Iliich246\YicmsFeedback\Base\FeedbackStagesDevTranslateForm */

?>

<?= $form->field($translateModel, "[$translateModel->key]name")->textInput() ?>

<?= $form->field($translateModel, "[$translateModel->key]description")->textarea() ?>
