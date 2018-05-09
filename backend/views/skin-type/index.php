<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SkinTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '肤质解读';
$this->params['breadcrumbs'][] = $this->title;
?>
    <?php
    use yii\bootstrap\Modal;
use common\models\SkinType;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
    ]);
    Modal::end();
?>
<div class="skin-type-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '5%'],
            ],

            [
                'attribute' => 'name',
                'options' => ['width' => '10%'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->name,'javascript:viod(0)',['title' => SkinType::getTestType($model->name_en)]);
                }
            ],
            [
                'attribute' => 'name_en',
                'options' => ['width' => '5%'],
            ],
            [
                'label' => '分值',    
                'options' => ['width' => '10%'],
                'value' => function ($model) {
                    $min = empty($model->min) ? '0' : $model->min;
                    $max = empty($model->max) ? '0' : $model->max;
                    return $min.'-'.$max;
                }
            ],
            [
                'attribute' => 'unscramble',
                'options' => ['width' => '800px'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}',
                'header' => '操作',
                'options' => ['width' => '80px'],
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
    var url = '/skin-type/update';
    $.get(url, { id: id },
        function (data) {
            $('.modal-body').html(data);
        }  
    );
});
JS;
$this->registerJs($script);
?>