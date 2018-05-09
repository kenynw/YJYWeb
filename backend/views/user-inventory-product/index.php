<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\functions\Tools;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserInventoryProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '清单产品';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-inventory-product-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => '产品名',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->productDetails->product_name,['product-details/view','id'=>$model->product_id]);
                }
            ],
            [
                'attribute' => 'desc',
                'value' => function ($model) {
                    return Tools::userTextDecode($model->desc);
                }
            ],
        ],
    ]); ?>
</div>
