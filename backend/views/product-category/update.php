<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductCategory */

// $this->title = 'Update Product Category: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '产品分类', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '编辑';
?>
<div class="product-category-update">

    <?= $this->render('_form', [
        'model' => $model,
        'parentArr' => $parentArr
    ]) ?>

</div>
