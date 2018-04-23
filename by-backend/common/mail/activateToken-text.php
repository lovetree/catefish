<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/activate', 'token' => $user->activate_token]);
?>
Hello <?= $user->username ?>,

Click the link below to activate your account:

<?= $resetLink ?>
