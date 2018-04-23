<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ChargeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '充值管理-充值记录';
$this->params['breadcrumbs'][] = '充值管理';
$this->params['breadcrumbs'][] = '充值记录';
?>
<div class="charge-index">
    <? //$this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'order_no',
            'paid_at',
            'account',
            'amount',
             'pay_status',
            'pay_type',
            // 'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
