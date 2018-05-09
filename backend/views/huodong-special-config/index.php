<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\HuodongSpecialConfigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '活动列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="huodong-special-config-index">
    <p>
        <?= Html::a('创建活动','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
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
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '2%'],
            ],
            [
                'attribute' => 'id',
                'options' => ['width' => '2%'],
            ],
            'name',
            'prize',
            [
                'attribute' => 'type',
                'options' => ['width' => '8%'],
                'value'     => function($model){
                    $arr = ['0' => 'H5页面','1' => '产品','2' => '文章'];
                    return $arr[$model->type];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['0' => 'H5页面','1' => '产品','2' => '文章'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'label' =>  'H5地址/产品,文章ID',
                'format'    => 'raw',
                'value'     => function($model){
                    if($model->type == 0){
                        return "H5链接：".Html::a($model->relation,$model->relation);
                    }else if($model->type == 1){
                        return "产品ID：".Html::a($model->relation,['product-details/index','ProductDetailsSearch[id]' => $model->relation]);
                    }else if($model->type == 2){
                        return "文章ID：".Html::a($model->relation,['article/index','ArticleSearch[id]' => $model->relation]);
                    }
                },
                'options' => ['width' => '15%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:15%'],
            ],
            'prize_num',
            're_number',
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
                'label' => '统计详情',
                'format'    => 'raw',
                'value'     => function($model){
//                     if ($model->sort == 0) {
//                         return Html::a('查看', ["huodong-special-config/act",'id'=> $model->id]);
//                     } else {
//                         return Html::a('查看', ["huodong-special-config/act$model->sort",'id'=> $model->id]);
//                     }
                    return Html::a('查看', ["huodong-special-config/act".$model->id,'id'=> $model->id]);
                },
            ],
            [
                'attribute' => 'starttime',
                'value' => function ($model) {
                    return date('Y-m-d H:i:s',$model->starttime);
                }
            ],
            [
                'attribute' => 'endtime',
                'value' => function ($model) {
                    return date('Y-m-d H:i:s',$model->endtime);
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp{delete}&nbsp&nbsp&nbsp{deldata}',
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
                    'deldata' => function ($url, $model, $key) {
                    if ($model->id == '3' && yii::$app->user->identity->id == 2) {
                        return '|'.Html::a('清除数据',["huodong-special-config/deldata",'id'=> $model->id],['class' => 'self']);
                    }
                    },
                ]
            ],
        ],
    ]); ?>
</div>

<?php
$script = <<<JS
//ajax修改页面状态  
status_ajax("/huodong-special-config/change-status");

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
    
//创建修改modal
$('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/huodong-special-config/create';
    } else {
        $('.modal-title').html("编辑分类");
        var url = '/huodong-special-config/update';
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
