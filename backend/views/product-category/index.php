<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


//搜索后展示时间
$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');

$this->title = '产品分类页';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-index">
    <p>
        <?= Html::a('添加分类',['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => '序号',
            ],

            'id',
            [
                'attribute' => 'cate_name',
                'format' => 'raw',
                'value' => function($model) {
                    return $model->parent_id == $model->id ? '<strong>'.$model->cate_name.'</strong>' : '|__ '.$model->cate_name;
                }
            ],
            [
                'attribute' => 'parent_id',
                'format' => 'raw',
                'value'     => function($model) use($parentArr){
//                     return empty($parentArr[$model->parent_id]) ? '' : $parentArr[$model->parent_id];
                    return '';
                },
                'options' => ['width' => '10%'],
                'filter' => Html::activeDropDownList($searchModel,
                    'parent_id',$parentArr,
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'product_num',
                'format' => 'raw',
                'value' => function($model) {
                    return $model->parent_id == $model->id ? '' : (!empty($model->product_num) ? Html::a($model->product_num, ['/product-details/index','ProductDetailsSearch[cate_id]'=> $model->id]) : 0);
                }
            ],
            [
                'attribute' => 'cate_h5_img',
                'format' => 'raw',
                'value' => function($model) {
                    return $model->parent_id == $model->id ? '' : Html::img(Yii::$app->params['uploadsUrl'] . $model->cate_h5_img,['width' => '100','height' => '50']);
                }
            ],
            [
                'attribute' => 'cate_app_img',
                'format' => 'raw',
                'value' => function($model) {
                    return $model->parent_id == $model->id ? '' : Html::img(Yii::$app->params['uploadsUrl'] . $model->cate_app_img,['width' => '100','height' => '50']);
                }
            ],
            'sort',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model){
                    if($model->status == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='status' data-id='".$model->id."'>已上架</button>";
                    }else{
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='status' data-id='".$model->id."'>已下架</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'status',['0' => '已下架','1' => '已上架'],
                ['prompt' => '所有'])
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;'])
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'header' => '操作',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', "/product-category/update?id=$model->id&parent_id=$model->parent_id");
                    },
                ]
            ],
        ],
    ]); ?>
</div>

<?php 
$script = <<<JS
$(function(){
//时间搜索框
    $('#start_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    });
    $('#end_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    }); 
});
//ajax修改页面状态   
status_ajax("/product-category/change-status");

JS;
$this->registerJs($script);
?>
