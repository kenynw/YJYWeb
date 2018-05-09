<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Ranking */

$this->title = '修改排行榜: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => '分类排行榜设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ranking-update">

    <?= $this->render('_form', [
        'model' => $model,
        'productList' => $productList,
        'uplen' => $uplen
    ]) ?>

</div>
