<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\AccountSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="account-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

<!--    --><?//= $form->field($model, 'id') ?>

    <?= $form->field($model, 'account')->textInput(['style'=>'width:200px']) ?>

    <?= $form->field($model, 'nickname')->textInput(['style'=>'width:200px'])  ?>

    <?= $form->field($model, 'mobile_phone')->textInput(['style'=>'width:200px'])  ?>

    <?= $form->field($model, 'email')->textInput(['style'=>'width:200px'])  ?>

    <?php // echo $form->field($model, 'real_name') ?>

    <?php // echo $form->field($model, 'id_card') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'diamond') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('查询', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
