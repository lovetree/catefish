<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\DownloadInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Download Infos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="download-info-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Download Info', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'platform',
            'version',
            'packagesize',
            'lan',
            // 'MD5',
            // 'download1',
            // 'download2',
            // 'uptime:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
