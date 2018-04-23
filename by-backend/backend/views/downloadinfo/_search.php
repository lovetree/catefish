<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\DownloadInfoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="download-info-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'platform') ?>

    <?= $form->field($model, 'version') ?>

    <?= $form->field($model, 'packagesize') ?>

    <?= $form->field($model, 'lan') ?>

    <?php // echo $form->field($model, 'MD5') ?>

    <?php // echo $form->field($model, 'download1') ?>

    <?php // echo $form->field($model, 'download2') ?>

    <?php // echo $form->field($model, 'uptime') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
