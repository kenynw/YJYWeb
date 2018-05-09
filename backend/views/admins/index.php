<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use mdm\admin\components\Helper;

$this->title = '管理员列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-index">

    <p>
        <?php
            if (Helper::checkRoute('create')) {
                echo Html::a('<i class="glyphicon glyphicon-plus"></i> 创建管理员','javascript:void(0)', ['class' => 'btn btn-success updates','data-target'=>'#update-modal','data-toggle'=>'modal',"data-id"=>""]);
            }
        ?>
    </p>

    <?php
    //创建马甲modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">创建</h4>',
        //'size' => "modal-lg",
    ]);
    Modal::end();
    ?>


    <?php
    $columns = [
        [
            'class' => 'yii\grid\SerialColumn',
            'header' => '序号',
        ],
        'id',
        'username',
        [
            'attribute' => 'status',
            'format' => 'raw',
            'value' => function($model){
                return ($model->status == 10) ? '正常' : '已封号';
            },
            'filter' => Html::activeDropDownList($searchModel,
                    'status',['10'=>'正常','0'=>'已封号'],
                    ['prompt'=>'所有']                    
                )
        ],
        [
            'attribute' => 'created_at',
            'format' => 'raw',
            'value' => function($model){
                return date('Y-m-d H:i:s', $model->created_at);
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'options'=>['style'=>'width:20%;'],
            'template' => Helper::checkRoute('create') ? '{update}&nbsp;&nbsp;{delete}' : '{view}',
            'header' => '操作',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a('','javascript:void(0)',[
                        'data' => [
                            'target' => '#update-modal',
                            'toggle' => 'modal',
                            'id' => $model->id
                        ],
                        'class' => 'glyphicon glyphicon-pencil updates'
                    ]);
                },
                'view' => function ($url, $model, $key) {
                    return Html::a('','javascript:void(0)',[
                        'data' => [
                            'target' => '#update-modal',
                            'toggle' => 'modal',
                            'id' => $model->id,
                            'view' => true
                        ],
                        'class' => 'glyphicon glyphicon-eye-open updates'
                    ]);
                },

            ]
        ],
    ];
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
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

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
});

//创建修改modal
$(document).on("click",'.updates',function(){
    var id = $(this).attr("data-id");
    var view = $(this).attr("data-view");
    var type = "ajax";
    
    if(id){
        if(view){ 
            $('.modal-title').html("详情");
            var url = '/admins/view';
        } else {
            $('.modal-title').html("修改密码");
            var url = '/admins/update';
        }
    }else{
        $('.modal-title').html("添加管理员");
        var url = '/admins/create';
    }

    $.get(url, { id: id,type: type },
        function (data) {
            $('.modal-body').html(data);
        }  
    );
    
});



JS;
$this->registerJs($script);
?>
