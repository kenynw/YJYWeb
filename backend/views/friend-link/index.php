<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FriendLinkSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '友链列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="friend-link-index">
    <p>
        <?= Html::a('添加友链','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
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
            ['class' => 'yii\grid\SerialColumn'],

            'link_id',
            'link_name',
            'link_url:url',
//             'link_logo',
//             'show_order',

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
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
$('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/friend-link/create';
    } else {
        $('.modal-title').html("编辑友链");
        var url = '/friend-link/update';
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