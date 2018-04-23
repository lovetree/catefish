<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\NewsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '公告列表';
$this->params['breadcrumbs'][] = "公告管理";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-index">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('发布文稿', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
//            'content',
//            'type',
             'publish_admin',
             'publish_time',
            // 'created_at',
             'updated_at',
             'status',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
