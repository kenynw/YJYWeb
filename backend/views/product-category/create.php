<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ProductCategory */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '产品分类', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-create">

    <?= $this->render('_form', [
        'model' => $model,
        'parentArr' => $parentArr
    ]) ?>

</div>
