<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Siteinfo */

$this->title = '添加栏目';
$this->params['breadcrumbs'][] = ['label' => '联系方式', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siteinfo-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
