<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Admin;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AdminLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '管理员操作记录';
$this->params['breadcrumbs'][] = $this->title;

$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');

?>
<div class="admin-log-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'route',
                'contentOptions' => ['style' => 'width:30%;word-break: break-all'],
            ],
            [
                'attribute' => 'username',
                'value' => function ($model) {
                    return $model->username;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'user_id',yii\helpers\ArrayHelper::map(Admin::find()->all(), 'id', 'username'),
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'description',
                'format' => 'html',
                'contentOptions' => ['style' => 'width:30%;word-break: break-all'],
                'value' => function ($model) {
                    if (preg_match ('/\<\$\>/',$model->description,$ma)) {
                        $strArr = explode('<$>', $model->description);
                    } else {
                        $strArr = explode(';', $model->description);
                    }
                    $desArr = explode('{$}', strip_tags($strArr[1]));
                    $desStr = '';
                    foreach ($desArr as $key=>$val) {
                        $desStr .= $val.'<br>';
                    }
                    return $strArr[0].$desStr;
                }
            ],
            [
                'attribute' => 'created_at',
                'options' => ['width' => '15%'],
                'value'     => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ." -- ".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;']),
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'header' => '操作',
            ],
        ],
    ]); ?>
</div>
<?php
$script = <<<JS
$(function(){
     $('#start_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    });
    $('#end_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    }); 
})
JS;
$this->registerJs($script);
?>
