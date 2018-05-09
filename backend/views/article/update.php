<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Article */

$this->title = '编辑文章页';
$this->params['breadcrumbs'][] = ['label' => '文章列表页', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '文章编辑';
?>
<div class="article-update">

    <?= $this->render('_form', [
        'model' => $model,
        'tagIdArr' => $tagIdArr,
        'skinList' => $skinList,
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]) ?>

</div>
