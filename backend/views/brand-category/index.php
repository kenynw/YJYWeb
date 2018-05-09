<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\BrandCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '品牌分类';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-category-index">
    <p>
         <?= Html::a('添加分类','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
    </p>
    <?php
    use yii\bootstrap\Modal;
use common\models\BrandCategory;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
        'size' => "modal-sm"
    ]);
    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => '序号',
            ],

            'id',
            'name',
            [
                'label' => '品牌数',
                'format' => 'raw',
                'value' => function ($model) {
                    return empty(BrandCategory::getBrand($model->id)) ? '0' : Html::a(BrandCategory::getBrand($model->id), ['brand/index','BrandSearch[cate_id]'=> $model->id]);
                }
            ],
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
            'sort',
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
//                 'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;'])
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'header' => '操作',
                'buttons' => [                 
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
                            'data-toggle' => 'modal',
                            'data-target' => '#update-modal',
                            'class' => 'data-update',
                            'data-id' => $key,
                        ]);
                    },
                ]
            ],
        ],
    ]); ?>
</div>
<?php 
$script = <<<JS
//ajax修改页面状态   
status_ajax("/brand-category/change-status");

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/brand-category/create';
    } else {
        $('.modal-title').html("编辑分类");
        var url = '/brand-category/update';
    }
        $.get(url, { id: id },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
JS;
$this->registerJs($script);


?>