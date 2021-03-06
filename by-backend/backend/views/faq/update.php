<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Faq */

$this->title = 'Update Faq: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'FAQ管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="faq-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
