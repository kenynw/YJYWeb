<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Advertisement */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => '广告列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '编辑广告';
?>
<div class="advertisement-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
