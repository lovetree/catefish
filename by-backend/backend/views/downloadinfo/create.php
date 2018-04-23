<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\DownloadInfo */

$this->title = 'Create Download Info';
$this->params['breadcrumbs'][] = ['label' => 'Download Infos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="download-info-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
