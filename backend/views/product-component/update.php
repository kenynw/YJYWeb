<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductComponent */

$this->title = '成分编辑: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Product Components', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-component-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
