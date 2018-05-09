<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Video;

/* @var $this yii\web\View */
/* @var $searchModel common\models\VideoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $type == '1' ? '站内视频列表' : '站外视频列表';
$this->params['breadcrumbs'][] = $this->title;

//搜索后展示时间
$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');
?>
<div class="video-index">
    <p>
        <?= $type == '1' ? Html::a('上传视频',['create'], ['class' => 'btn btn-success']) : Html::a('抓取视频','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
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
    <?php if ($type == '2') {
        Pjax::begin([
            'enablePushState' => false,
            'timeout'         => 10000,
            'id'              =>'w1'
        ]);
    }?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function ($model) use ($type) {
                    return $type == '1' ? Html::a($model->title,['video/view','id'=>$model->id],['data-pjax'=>'false','target'=>'_blank']) : $model->title;
                },
                'contentOptions' => ['style' => 'white-space:pre-wrap;'],
            ],
            [
                'attribute' => 'duration',
                'visible' => $type == 1 ? '1' : '0',
                'value' => function ($model) {
                    return $model->duration;
                }
            ],
            [
                'label' => '产品数',
                'visible' => $type == 1 ? '1' : '0',
                'options' => ['width' => '5%'],
                'format'    => 'raw',
                'value' => function ($model) {
                    $product_num = empty($model->product_id) ? '0' : count(explode(',', $model->product_id));
                    $title = empty($model->product_id) ? '' : '<p align="left">'. Video::getProductStr($model->id).'</p>';
                    return empty($product_num) ? 0 : Html::a($product_num, 'javascript:void(0)',['title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right','data-html'=>'true']);
                }
            ],
//             [
//                 'attribute' => 'is_complete',
//                 'visible' => $type == 1 ? '0' : '1',
//                 'format' => 'raw',
//                 'value' => function($model){
//                     return empty($model->is_complete) ? '抓取中' : '已完成';
//                 },
//                 'filter' => Html::activeDropDownList($searchModel,
//                     'is_complete',['0' => '抓取中','1' => '已完成'],
//                     ['prompt' => '所有'])
//             ],
            [
                'attribute' => 'status',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'visible' => $type == 1 ? '1' : '0',
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
                'attribute' => 'update_time',
                'format' => 'raw',
                'options' => ['width' => '15%'],
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->update_time);
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;']),
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{download}&emsp;{update}&nbsp;{delete}',
                'header' => '操作',
                'options' => ['width' => '5%'],
                'buttons' => [         
                    'update' => function ($url, $model, $key) use ($type) {
                        return $type == 1 ? Html::a('<span class="glyphicon glyphicon-pencil"></span>', "/video/update?id=$model->id", [
//                             'data-toggle' => 'modal',
//                             'data-target' => '#update-modal',
//                             'class' => 'data-update',
//                             'data-id' => $key,
                        ]) : '';
                    },
                    'download' => function ($url, $model, $key) use ($type) {
                        return Html::a('<span class="glyphicon glyphicon-download-alt"></span>', "/video/download?id=$model->id&type=$type", [
                            'class' => 'self',
                            'title' => '下载',
                            'data-method' => 'post',
                            'data-pjax' => '0'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) use ($type) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', "/video/delete?id=$model->id&type=$type", [
                            'class' => 'self',
                            'title' => '删除',
                            'data-method' => 'post',
                            'data-pjax' => '0',
                            'data-confirm' => "您确定要删除此项吗？"  
                        ]);
                    },
                ]
            ],
        ],
    ]); ?>
</div>
<?php if ($type == '2') {
    Pjax::end();
}?>

<?php 
$script = <<<JS
$(function(){
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
// //创建修改modal
$('.data-update').on('click', function () {
    if($type == '1') {
//         var id = $(this).attr("data-id");
//         if (id == '') {
//             $('.modal-title').html("添加");
//             var url = '/video/create';
//         } else {
//             $('.modal-title').html("编辑");
//             var url = '/video/update';
//         }
//         $.get(url, { id: id },
//             function (data) {
//                 $('.modal-body').html(data);
//             }  
//         );
    }else{
        $('.modal-title').html("抓取视频");
        var url = '/video/create2';
        $.get(url, {},
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    }
});
    
//ajax修改页面状态  
status_ajax("/video/change-status");
    
// 时间搜索框
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

JS;
$this->registerJs($script);
?>