<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Faq */

$this->title = '添加FAQ';
$this->params['breadcrumbs'][] = '客服管理';
$this->params['breadcrumbs'][] = ['label' => 'FAQ管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="faq-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
