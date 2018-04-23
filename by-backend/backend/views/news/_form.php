<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\News */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="news-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->widget('kucha\ueditor\UEditor') ?>

    <?= $form->field($model, 'type')->radioList(['公告', '活动', '心得攻略']) ?>


    <?= $form->field($model, 'intended_time')->widget('kartik\datetime\DateTimePicker', [
        'pluginOptions'=>[
            'format'=>'yyyy-mm-dd H:i:s'
        ]
    ])?>

    <div class="form-group">
        <?= Html::submitButton('确定', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
