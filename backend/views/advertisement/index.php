<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Advertisement;
use backend\assets\AppAsset;
AppAsset::register($this);

$this->title = '广告列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="advertisement-index">

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 新增广告','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
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
                'options' => ['width' => '17%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:20%'],
            ],
            [
                'header' => '投放位置',
                'format'    => 'raw',
                'options' => ['width' => '15%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:15%'],
                'value'     => function($model){
                    $typeList = Advertisement::getType();
                    $position = ['main'=>'主体','left'=>'右边栏'];
                    $data = $typeList[$model->type] . " --- " . $position[$model->position] . $model->sort;
                    return $data;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type',Advertisement::getType(),
                    ['prompt' => '所有'])
            ],
            [
                'format'    => 'raw',
                'attribute' => 'img',
                'options' => ['width' => '12%'],
                'value'     => function($model){
                    return $model->img ? Html::img(Yii::$app->params['uploadsUrl'] . $model->img,['class' => 'images', 'height' => '50px']).'&nbsp;' : "";
                },
            ],

            [
                'format'    => 'raw',
                'attribute' => 'url',
                'options' => ['width' => '16%'],
                'value'     => function($model){
                    return $model->url ? Html::a($model->url , $model->url) : "";
                },
            ],
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
                'header' => '有效期',
                'format' => 'raw',
                'value'     => function($model){
                    $data = "";
                    if(time() > $model->end_time){
                        $data = '<br/><span class="label label-danger">已过期</span>';
                    }
                    return date('Y-m-d', $model->start_time) . " ~ " . date('Y-m-d', $model->end_time) . $data;
                },
                'options' => ['width' => '15%'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'header' => '操作',
                'options' => ['width' => '12%'],
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
AppAsset::addScript($this,'@web/js/artDialog4.1.6/jquery.artDialog.source.js?skin=idialog');

$script = <<<JS

$('.images').on('click', function(){
    
    var max_width = ((document.body.clientWidth) * 0.8) + "px";
    var src = $(this).attr("src");
    var content = '<img src="' + src + '" style="max-width:' + max_width + '">';
    
    art.dialog({
        content : content,
        //width : "710px",
        title : '图片预览',
        padding : "40px 70px",
        lock : true,
        opacity : .4,
        time : 2,
        //top: '5%',				// Y轴坐标
    });
    
});

//ajax修改页面状态  
status_ajax("/advertisement/change-status");


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
        var url = '/advertisement/create';
    } else {
        $('.modal-title').html("更新");
        var url = '/advertisement/update';
    }
    $.get(url, { id: id },
        function (data) {
            $('.modal-body').html(data);
            
            if(type == "view"){
                $(".advertisement-form").find("input").attr("disabled",true);
                $(".advertisement-form").find("select").attr("disabled",true);
                $(".advertisement-form").find("textarea").attr("disabled",true);
                $(".advertisement-form").find("button").css("display",'none');
                $(".advertisement-form").find("a").css("display",'none');
            }else{
                $(".advertisement-form").find("input").attr("disabled",false);
                $(".advertisement-form").find("select").attr("disabled",false);
                $(".advertisement-form").find("textarea").attr("disabled",false);
                $(".advertisement-form").find("button").css("display",'block');    
            }
        }  
    );
});

JS;
$this->registerJs($script);
?>
