<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户查询';
$this->params['breadcrumbs'][] = '游戏用户';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="account-index">
    <? //$this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            'account',
            'nickname',
            'mobile_phone',
            'email:email',
             'real_name',
             'id_card',
             'created_at',
             'diamond',
             'status',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
