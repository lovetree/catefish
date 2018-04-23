<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\DownloadInfo */

$this->title = 'Update Download Info: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Download Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="download-info-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
