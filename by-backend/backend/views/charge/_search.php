<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ChargeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="charge-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'order_no') ?>

    <?= $form->field($model, 'paid_at') ?>

    <?= $form->field($model, 'account') ?>

    <?= $form->field($model, 'amount') ?>

    <?= $form->field($model, 'pay_status')->dropDownList(['weizhifu'=>'未支付']) ?>

    <?= $form->field($model, 'pay_type') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
