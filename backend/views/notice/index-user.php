<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\NoticeSystemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '活动通知';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notice-system-index">
    <?php
    use yii\bootstrap\Modal;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
    ]);
    Modal::end();
    ?>
    
    <p>
        <?= Html::a('创建活动','javascript:void(0)', ['class' => 'btn btn-info data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'label'=>'活动内容',
                'attribute' => 'content',                
                'format' => 'raw',
                'value' => function($model){
                    return '<div style="white-space: normal;">'.$model->content.'</div>';
                },
            ],
            [
                'label' =>  'H5地址/产品,文章ID',
                'format'    => 'raw',
                'value'     => function($model){
                    if($model->type == 2){
                        return "H5链接：".Html::a($model->relation,$model->relation);
                    }else if($model->type == 4){
                        return "产品ID：".Html::a($model->relation,['product-details/index','ProductDetailsSearch[id]' => $model->relation]);
                    }else if($model->type == 3){
                        return "文章ID：".Html::a($model->relation,['article/index','ArticleSearch[id]' => $model->relation]);
                    }
                },
                'options' => ['width' => '15%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:15%'],
            ],
            [
                'attribute' => 'type',
                'options' => ['width' => '8%'],
                'value'     => function($model){
                    $arr = ['0' => '常规','2' => 'H5页面','4' => '产品详情','3' => '文章详情'];
                    return $arr[$model->type];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['0' => '常规','2' => 'H5页面','4' => '产品详情','3' => '文章详情'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model){
                    '<a href="#" class="btn btn-primary btn-lg disabled" role="button">Primary link</a>';
                    return $model->status?'<a href="#" class="btn btn-success btn-xs disabled" role="button">已发送</a>':'<a href="#" class="btn btn-default btn-xs disabled" role="button">未发送</a>';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'status', ['未发送','已发送'],         
                    ['prompt'=>'所有']
                ),
                'options' => ['width' => '5%']
            ],
//            [
//                'attribute' => 'created_at',
//                'format' => 'raw',
//                'value' => function($model){
//                    return date('Y-m-d H:i:s', $model->created_at);
//                },
//            ],
            [
                'label'=>'发送时间',
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function($model){
                    if(!$model->status){
                        return '';
                    }
                    return date('Y-m-d H:i:s', $model->updated_at);
                },
                'options' => ['width' => '10%']
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{send} {update} {delete}',
                'header' => '操作',
                'buttons' => [
                
                'send' => function ($url, $model, $key) {
                    if($model->status){
                        return '';
                    }
                    
                    $options = [
                        'title' => Yii::t('yii', '发送'),
                        'aria-label' => Yii::t('yii', '发送'),
                        'data-pjax' => '0',
                         'data' => [
                                    'confirm' => '确定发送？',
                                    'method' => 'post',
                                ],
                        'class'=>'btn btn-primary btn-xs',
                    ];
                    return Html::a('发送', ['send','id' => $model->id], $options);
                },
                'update' => function ($url, $model, $key) {
                    if($model->status){
                        return '';
                    }
                    return Html::a('<span class="btn btn-info btn-xs">编辑</span>', 'javascript:void(0)', [
                        'data-toggle' => 'modal',
                        'data-target' => '#update-modal',
                        'class' => 'data-update',
                        'data-id' => $key,
                    ]);
                },
                'delete' => function ($url, $model, $key) {
                    $options = [
                        'title' => Yii::t('yii', '删除'),
                        'aria-label' => Yii::t('yii', '删除'),
                        'data-pjax' => '0',
                        'data' => [
                                    'confirm' => '确定删除？',
                                    'method' => 'post',
                                ],
                        'class'=>'btn btn-danger btn-xs',
                    ];
                    return Html::a('删除', ['delete-user', 'id' => $model->id], $options);
                },
            ],
            ],
        ],
        'pager'=>[
            'firstPageLabel' => "首页",
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '最后一页',
        ],
    ]); ?>
</div>

<?php

$script = <<<JS
$('form').on('beforeValidate', function (e) {
    $(':submit').attr('disabled', true).addClass('disabled');
});
$('form').on('afterValidate', function (e) {
    if (cheched = $(this).data('yiiActiveForm').validated == false) {
        $(':submit').removeAttr('disabled').removeClass('disabled');
    }
});
$('form').on('beforeSubmit', function (e) {
    $(':submit').attr('disabled', true).addClass('disabled');
});
    
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("新活动");
        var url = '/notice/create-user';
    } else {
        $('.modal-title').html("编辑分类");
        var url = '/notice/update-user';
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