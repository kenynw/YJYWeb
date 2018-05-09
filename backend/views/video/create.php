<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Video */

$this->title = '添加视频';
$this->params['breadcrumbs'][] = ['label' => '站内视频列表', 'url' => ['index1']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="video-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
