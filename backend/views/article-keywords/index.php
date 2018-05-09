<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ArticleKeywordsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '文章内链';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-keywords-index">
    <?php echo $this->render('_import'); ?>
    <p>
        <?= Html::a('添加内链','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
        <?= Html::a('更新内链',['update-link'],['class' => 'btn btn-info','data-confirm' => '您确定要更新全部文章内链吗？']) ?>
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
            ['class' => 'yii\grid\SerialColumn'],

            'keyword',
            [
                'attribute' => 'link',
                'format' => 'url',
                'options' => ['width' => '60%'] 
            ],

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
        var url = '/article-keywords/create';
    } else {
        $('.modal-title').html("编辑分类");
        var url = '/article-keywords/update';
    }
        $.get(url, { id: id },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
})
JS;
$this->registerJs($script);
?>
