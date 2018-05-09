<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Banner;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\BannerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Banners';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="banner-index">

    <p>
        <?= Html::a('创建 Banner','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
    </p>

    <?php
    use yii\bootstrap\Modal;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
        'size' => "modal-lg",
    ]);
    Modal::end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => '序号',
                'headerOptions'=> ['width'=> '5%'],
            ],
            [
                'attribute' => 'title',
                'format'    => 'raw',
                'value'     => function($model){
                    return Html::a($model->title,'javascript:void(0)', ['class' => 'data-update','data-target'=>'#update-modal','data-toggle'=>'modal',"data-id"=>$model->id,"data-type"=>'view']);
                },
                'options' => ['width' => '15%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:15%'],
            ],
            [
                'label' =>  'H5地址/产品,文章ID',
                'format'    => 'raw',
                'value'     => function($model){
                    if($model->type == 1){
                        return "H5链接：".Html::a($model->url,$model->url);
                    }else if($model->type == 2){
                        return "产品ID：".Html::a($model->relation_id,['product-details/index','ProductDetailsSearch[id]' => $model->relation_id]);
                    }else if($model->type == 3){
                        return "文章ID：".Html::a($model->relation_id,['article/index','ArticleSearch[id]' => $model->relation_id]);
                    }else if($model->type == 5){
                        return "话题ID：".Html::a($model->relation_id,['topic/index','TopicSearch[id]' => $model->relation_id]);
                    }else{
                        return "无跳转";
                    }
                },
                'options' => ['width' => '17%'],
                'contentOptions' => ['style' => 'word-break: break-all;max-width:15%'],
            ],
            [
                'attribute' => 'type',
                'options' => ['width' => '8%'],
                'value'     => function($model){
                     $arr = ['1'=>'H5页面','2'=>'产品详情','3'=>'文章详情','5'=>'话题详情','4'=>'无跳转'];
                    return $arr[$model->type];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['1'=>'H5页面','2'=>'产品详情','3'=>'文章详情','5'=>'话题详情','4'=>'无跳转'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'attribute' => 'position',
                'options' => ['width' => '8%'],
                'value'     => function($model){
                    $arr = ['1'=>'APP首页','2'=>'APP发现页','3'=>'H5首页'];
                    return $arr[$model->position];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'position', ['1'=>'APP首页','2'=>'APP发现页','3'=>'H5首页'],
                    ['prompt'=>'所有']
                )
            ],
//             [
//                 'attribute' => 'status',
//                 'format' => 'raw',
//                 'options' => ['width' => '8%'],
//                 'value' => function($model){
//                     return $model->status ? " <span class='label label-success'>已上架</span>" : " <span class='label label-default'>已下架</span>";
//                 },
//                 'filter' => Html::activeDropDownList($searchModel,
//                     'status',['0' => '已下架','1' => '已上架'],
//                     ['prompt' => '所有'])
//             ],

           [
               'attribute' => 'status',
               'options' => ['width' => '8%'],
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
                'attribute' => 'sort_id',
                'options' => ['width' => '7%'],
                'value'     => function($model){
                    return $model->sort_id;
                },
            ],
            [
                'format'    => 'raw',
                'attribute' => 'img',
                'options' => ['width' => '8%'],
                'value'     => function($model){
                    return Html::img(Yii::$app->params['uploadsUrl'] . $model->img,['height' => '50px']);
                },
            ],
//             [
//                 'attribute' => 'start_time',
//                 'value'     => function($model){
//                     return date('Y-m-d H:i:s', $model->start_time);
//                 },
//                 'options' => ['width' => '12%'],
//             ],
//             [
//                 'attribute' => 'end_time',
//                 'value'     => function($model){
//                     return date('Y-m-d H:i:s', $model->end_time);
//                 },
//                 'options' => ['width' => '12%'],
//             ],

            [
            'header' => '有效期',
            'format' => 'raw',
            'value'     => function($model){
                $data = "";
                if(time() > $model->end_time){
                    $data = '<br/><span class="label label-danger">已过期</span>';
                }
                return date('Y-m-d H:i:s', $model->start_time) . " ~ " . date('Y-m-d H:i:s', $model->end_time) . $data;
            },
            'options' => ['width' => '10%'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
                'options' => ['width' => '6%'],
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
        'pager' => [
            'firstPageLabel' => "首页",
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '最后一页',
        ],
    ]); ?>
</div>
<?php
$script = <<<JS

//ajax修改页面状态  
status_ajax("/banner/change-status",1);

$(function(){
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
})

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
$('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    var type = $(this).attr("data-type");
    
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/banner/create';
    } else {
        $('.modal-title').html("更新");
        var url = '/banner/update';
    }
    $.get(url, { id: id },
        function (data) {
            $('.modal-body').html(data);
            
            if(type == "view"){
                $(".banner-form").find("input").attr("disabled",true);
                $(".banner-form").find("select").attr("disabled",true);
                $(".banner-form").find("textarea").attr("disabled",true);
                $(".banner-form").find("button").css("display",'none');
                $(".banner-form").find("a").css("display",'none');
            }else{
                $(".banner-form").find("input").attr("disabled",false);
                $(".banner-form").find("select").attr("disabled",false);
                $(".banner-form").find("textarea").attr("disabled",false);
                $(".banner-form").find("button").css("display",'block');    
            }
        }  
    );
});

JS;
$this->registerJs($script);
?>