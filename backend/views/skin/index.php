<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ProductCategory;
use backend\models\CommonFun;
use common\models\ProductComponent;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SkinSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
//分类
$cateid = ProductCategory::find()->asArray()->all();
$cateList = [];
$cateList['0'] = '未设置';
foreach ($cateid as $key=>$val) {
    $cateList[$val['id']] = $val['cate_name'];
}

$this->title = '肤质列表';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="skin-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['style'=>'width:5px']
            ],

            [
                'attribute' => 'skin',
                'options' => ['style'=>'width:17%;text-align:center'],
                'contentOptions' => ['style'=>'text-align:center;line-height:20px'],
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->skin."<br>".$model->explain, ['view','id'=> $model->id]);
                }
            ],
            [
                'attribute' => 'features',
                'options' => ['style'=>'width:34%']
            ],
            [
                'attribute' => 'elements',
                'options' => ['style'=>'width:34%']
            ],
            [
                'label' => '文章数',
                'format' => 'raw',
//                 'options' => ['style'=>'width:100px'],
                'value' => function ($model) {
                    return empty(count($model->article)) ? count($model->article) : Html::a(count($model->article),['article/index','ArticleSearch[skin_id]'=> $model->id]);
                }
            ],
//             [
//                 'attribute' => 'recomponent',
//                 'options' => ['style'=>'width:25%'],
//                 'value' => function ($model) {
//                     return empty($model->recomponent) ? '' : join('，',CommonFun::getConnectArr(explode(',',$model->recomponent),new ProductComponent(),'id','name'));
//                 }
//             ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{refresh}',
                'header' => '操作',
//                 'options' => ['style'=>'width:100px'],
                'buttons' => [
                    'refresh' => function ($url, $model, $key) {
                        if (yii::$app->user->identity->id == 2) {
                            return Html::a('<span class="glyphicon glyphicon-repeat"></span>', '/skin/refresh?skin='.$model->skin, [
                                'data-method' => 'post',
                                'data-pjax' => '0',
                                'class' => 'self',
                                'title' => '获取推荐成分产品数据'
                            ]);
                        } else {
                            return '';
                        }
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
    $('.modal-title').html('肤质类型：'+$(this).attr('data-title'));
    var url = '/skin/update';
    $.get(url, { id: $(this).closest('tr').data('key') },
        function (data) {
            $('.modal-body').html(data);
        }  
    );
    });
JS;
$this->registerJs($script);
?>
