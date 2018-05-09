<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TopicSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '话题列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="topic-index">
    <p>
        <?= Html::a('创建话题','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
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
        'options' => ['class' => ''],
        'columns' => [

            [
                'attribute' => 'id',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'title',
                'options' => ['width' => '20%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:15%'],
            ],
            [
                'attribute' => 'desc',
                'options' => ['width' => '25%'],
                'contentOptions' => ['style' => 'word-break: break-all;max-width:17%'],
            ],
            [
                'attribute' => 'post_num',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function ($model) {
                    return empty($model->post_num) ? '-' : Html::a($model->post_num, ['post/index','PostSearch[topic_id]'=> $model->id,'PostSearch[topic_title]'=> $model->title]);
                }
            ],
            [
                'attribute' => 'picture',
                'options' => ['width' => '10%'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img(Yii::$app->params['uploadsUrl'] . $model->picture,['height' => '50px']);
                }              
            ],
            [
                'attribute' => 'share_pic',
                'options' => ['width' => '10%'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img(Yii::$app->params['uploadsUrl'] . $model->share_pic,['height' => '50px']);
                }
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
                'attribute' => 'created_at',
                'options' => ['width' => '10%'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
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
status_ajax("/topic/change-status");

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加话题");
        var url = '/topic/create';
    } else {
        $('.modal-title').html("编辑话题");
        var url = '/topic/update';
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