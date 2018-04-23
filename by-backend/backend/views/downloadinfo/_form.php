<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\DownloadInfo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="download-info-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'platform')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'packagesize')->textInput() ?>

    <?= $form->field($model, 'lan')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'MD5')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'download1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'download2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uptime')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
