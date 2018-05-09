<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\NoticeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '系统通知';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notice-index">
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
            ['class' => 'yii\grid\SerialColumn'],

//             'id',
            'title',
            'content',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'header' => '操作',
                'buttons' => [                 
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
                            'data-toggle' => 'modal',
                            'data-target' => '#update-modal',
                            'data-title'=>$model->title,
                            'class' => 'data-update',
                        ]);
                    },
                ]
            ],
        ],
    ]); ?>
</div>
<?php 
$script = <<<JS
//创建修改modal
 $('.glyphicon-pencil').on('click', function () {
    $('.modal-title').html($(this).attr('data-title'));
    var url = '/notice/update';
    $.get(url, { id: $(this).closest('tr').data('key') },
        function (data) {
            $('.modal-body').html(data);
        }  
    );
    });
JS;
$this->registerJs($script);
?>
