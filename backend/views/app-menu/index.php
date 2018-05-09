<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AppMenuSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'App频道列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-menu-index">
    <p>
        <?= Html::a('添加频道','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
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

            'id',
            'title',
            'subtitle',
            [
                'format'    => 'raw',
                'attribute' => 'img',
                'options' => ['width' => '8%'],
                'value'     => function($model){
                    return Html::img(Yii::$app->params['uploadsUrl'] . $model->img,['height' => '50px']);
                },
            ],
            'sort',
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
            'add_time',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
                'header' => '操作',
                'options' => ['width' => '5%'],
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
$(function(){
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/app-menu/create';
    } else {
        $('.modal-title').html("编辑频道");
        var url = '/app-menu/update';
    }
        $.get(url, { id: id },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
    
    //ajax修改页面状态  
    status_ajax("/app-menu/change-status");
})
JS;
$this->registerJs($script);
?>