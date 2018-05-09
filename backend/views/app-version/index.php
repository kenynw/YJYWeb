<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AppVersionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'App 版本设置';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-version-index">

    <p>
        <?= Html::a('新增', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' => 'content',
                'format'    => 'raw',
                'options' => ['width' => '25%'],
                'contentOptions' => ['style' => 'white-space:nowrap; overflow:hidden; text-overflow:ellipsis;max-width:50px;'],
            ],
            [
                'attribute' => 'number',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'type',
                'value'     => function($model){
                    return $model->type == 1 ? 'android' : 'ios';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['1'=>'android','2'=>'ios'],
                    ['prompt' => '所有']
                )
            ],

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
                'format'    => 'raw',
                'attribute' => 'downloadUrl',
                'value'     => function($model){
                    if($model->type == 1){
                        return Html::a(Yii::$app->params['frontendUrl'] . $model->downloadUrl,Yii::$app->params['frontendUrl'] . $model->downloadUrl);
                    }else{
                        return Html::a($model->downloadUrl,$model->downloadUrl);
                    }
                }
            ],

            [
                'attribute' => 'create_time',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->create_time);
                },
            ],

            //'downloadUrl:url',
            // 'isMust',
            // 'creater_id',
            // 'update_time:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>


<?php
$script = <<<JS

//ajax修改页面状态  
status_ajax("/app-version/change-status");

JS;
$this->registerJs($script);
?>