<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ProductBonus */

$this->title = '添加商品';
$this->params['breadcrumbs'][] = ['label' => '商城列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-bonus-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
