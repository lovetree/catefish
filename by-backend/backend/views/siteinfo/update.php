<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Siteinfo */

$this->title = 'Update Siteinfo: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '客服管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="siteinfo-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
