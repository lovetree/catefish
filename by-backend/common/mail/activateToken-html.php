<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/activate', 'token' => $user->activate_token, 'name'=>$user->username]);
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Click the link below to activate your account:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
