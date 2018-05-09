<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SkinRecommendProduct */

$this->title = 'Update Skin Recommend Product: ' . $model->skin_id;
$this->params['breadcrumbs'][] = ['label' => 'Skin Recommend Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->skin_id, 'url' => ['view', 'skin_id' => $model->skin_id, 'cate_id' => $model->cate_id, 'product_id' => $model->product_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="skin-recommend-product-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'cateList' => $cateList,
    ]) ?>

</div>
