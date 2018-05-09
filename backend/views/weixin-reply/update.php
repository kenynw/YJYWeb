<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\WeixinReply */

$this->title = '编辑微信自动回复';
$this->params['breadcrumbs'][] = ['label' => '微信自动回复列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="weixin-reply-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
