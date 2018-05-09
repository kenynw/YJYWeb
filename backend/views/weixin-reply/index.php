<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\WeixinReplySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '微信自动回复列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="weixin-reply-index">

    <p>
        <?= Html::a('添加', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

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
                'attribute' => 'type',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    $arr = ['0'=>'默认回复','1'=>'文本回复','2'=>'事件','3'=>'图片','4'=>'文章'];
                    return $arr[$model->type];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['0'=>'默认回复','1'=>'文本回复','2'=>'事件','3'=>'图片','4'=>'文章'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'attribute' => 'match_mode',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    $arr = ['contain'=>'部分匹配','equal'=>'完全匹配'];
                    return $arr[$model->match_mode];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'match_mode', ['contain'=>'部分匹配','equal'=>'完全匹配'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'attribute' => 'keyword',
                'options' => ['width' => '15%'],
            ],
            [
                'attribute' => 'reply',
                'options' => ['width' => '45%'],
                'format' => 'raw',
                'contentOptions' => ['style' => 'word-break: break-all;'],
                'value' => function ($model) {
                    return htmlspecialchars_decode($model->reply);
                }
            ],
            [
                'attribute' => 'add_time',
                'options' => ['width' => '10%'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
                'header' => '操作',
            ],
        ],
    ]); ?>
</div>

<?php 
$script = <<<JS


JS;
$this->registerJs($script);
?>