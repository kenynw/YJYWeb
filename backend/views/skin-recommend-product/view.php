<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\SkinRecommendProduct */

$this->title = $model->skin_id;
$this->params['breadcrumbs'][] = ['label' => 'Skin Recommend Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="skin-recommend-product-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'skin_id' => $model->skin_id, 'cate_id' => $model->cate_id, 'product_id' => $model->product_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'skin_id' => $model->skin_id, 'cate_id' => $model->cate_id, 'product_id' => $model->product_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'skin_id',
            'skin_name',
            'cate_id',
            'product_id',
        ],
    ]) ?>

</div>
