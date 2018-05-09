<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Post */

$this->title = '编辑帖子';
$this->params['breadcrumbs'][] = ['label' => '帖子列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = '编辑帖子';
?>
<div class="post-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
