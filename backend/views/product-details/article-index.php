<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\ProductCategory;
use yii\widgets\Pjax;

//分类
$cateid = ProductCategory::find()->asArray()->all();
$cateList = [];
$cateList['0'] = '未设置';
foreach ($cateid as $key=>$val) {
    $cateList[$val['id']] = $val['cate_name'];
}

?>

<div class="product-details-index-del">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => false,//$searchModel,
        "options" => [
            "id" => "grid-del"
        ],
        'columns' => [
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                "header"=>'<span style="display:;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>',
                'headerOptions' => ['width' => '8%']
            ],
            [
                'attribute' => 'id',
                'options' => ['width' => '12%']
            ],
            [
                'attribute' => 'product_name',
                'format' => 'raw',
                'value' => function($model){
                    return Html::a($model->product_name,['product-details/view','id'=>$model->id]);
                }
            ],
//                            'brand',
            [
                'attribute' => 'cate_id',
                'value'     => function($model){
                    return empty($model->productCategory->cate_name) ? '未设置' : $model->productCategory->cate_name;
                },
            ],
            [
                'attribute' => 'price',
                'options' => ['style' => 'width:70px'],
                'value' => function($model) {
                    return empty($model->price) ? '' : $model->price;
                }
            ],
            'form',
            [
                'attribute' => 'star',
                'format' => 'raw',
                'value' => function($model){
                    $stars = '';
                    for($i=0;$i<$model->star;$i++){
                        $stars .= "<span class='star-active-icon'></span>";
                    }
                    if ($model->star < 5) {
                        for($i=0;$i<5-($model->star);$i++){
                            $stars .= "<span class='star-icon'></span>";
                        }
                    }
                    return $stars;
                },
            ],
        ],
    ]); ?>
</div>

<div style="width:300px;height:120px;">
    <div style="clear:both;">
        <?= Html::a("确认删除", "javascript:void(0);", ["class" => "btn btn-danger delete-product"]) ?>
    </div>
</div>




