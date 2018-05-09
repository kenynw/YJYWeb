<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Brand */

$this->title = '编辑品牌页' ;
$this->params['breadcrumbs'][] = ['label' => '品牌列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '品牌编辑';
?>
<div class="brand-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
