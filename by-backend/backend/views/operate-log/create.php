<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\OperateLog */

$this->title = 'Create Operate Log';
$this->params['breadcrumbs'][] = ['label' => 'Operate Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operate-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
