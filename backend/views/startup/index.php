<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StartupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'app启动页';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="startup-index">
    <p>
        <?= Html::a('创建','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
    </p>
    
    <?php
    use yii\bootstrap\Modal;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
    ]);
    Modal::end();
    ?>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            [
                'attribute' => 'id',
//                 'options' => ['width' => '8%'],
            ],
            [
                'attribute' => 'title',
            //                 'options' => ['width' => '8%'],
            ],
            [
                'attribute' => 'type',
//                 'options' => ['width' => '8%'],
                'value'     => function($model){
                    $arr = ['0'=>'H5页面','1'=>'产品详情','2'=>'文章详情','3'=>'视频详情','4'=>'话题详情','5'=>'无跳转'];
                    return $arr[$model->type];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['0'=>'H5页面','1'=>'产品详情','2'=>'文章详情','3'=>'视频详情','4'=>'话题详情','5'=>'无跳转'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'label' =>  'H5地址/产品,文章ID',
                'format'    => 'raw',
                'value'     => function($model){
                    if($model->type == 0){
                        return "H5链接：".Html::a($model->url,$model->url);
                    }else if($model->type == 1){
                        return "产品ID：".Html::a($model->relation_id,['product-details/index','ProductDetailsSearch[id]' => $model->relation_id]);
                    }else if($model->type == 2){
                        return "文章ID：".Html::a($model->relation_id,['article/index','ArticleSearch[id]' => $model->relation_id]);
                    }else if($model->type == 3){
                        return "视频ID：".Html::a($model->relation_id,['video/index1','VideoSearch[id]' => $model->relation_id]);
                    }else if($model->type == 4){
                        return "话题ID：".Html::a($model->relation_id,['topic/index','TopicSearch[id]' => $model->relation_id]);
                    }else{
                        return "无跳转";
                    }
                },
//                 'options' => ['width' => '17%'],
                'contentOptions' => ['style' => 'word-break: break-all;max-width:15%'],
            ],
            [
                'attribute' => 'status',
//                 'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
//                     if($model->status == 1){
//                         return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='status' data-id='".$model->id."'>已上架</button>";
//                     }else{
//                         return "<button class='btn btn-xs btnstatus' data-status='0' data-type='status' data-id='".$model->id."'>已下架</button>";
//                     }
                    return $model->status ? " <span class='label label-success'>已上架</span>" : " <span class='label label-default'>已下架</span>";
                },
                'filter' => Html::activeDropDownList($searchModel,
                   'status',['0' => '已下架','1' => '已上架'],
                   ['prompt' => '所有'])
            ],
            [
                'format'    => 'raw',
                'attribute' => 'img',
//                 'options' => ['width' => '8%'],
                'value'     => function($model){
                    return Html::img(Yii::$app->params['uploadsUrl'] . $model->img,['height' => '50px']);
                },
            ],
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
//                 'options' => ['width' => '10%'],
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
    ]); ?>
</div>

<?php
$script = <<<JS

//ajax修改页面状态  
status_ajax("/startup/change-status",1);

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/startup/create';
    } else {
        $('.modal-title').html("编辑");
        var url = '/startup/update';
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