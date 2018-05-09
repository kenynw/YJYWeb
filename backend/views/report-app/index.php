<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\assets\AppAsset;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAppSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');
$referer = isset($_GET['ReportAppSearch']['referer']) ? $_GET['ReportAppSearch']['referer'] : '';
//默认显示最近7天
$date1 = !empty($date1) ? $date1 : date('Y-m-d',strtotime('-7 days'));
$date2 = !empty($date2) ? $date2 : date('Y-m-d',time());

$this->title = 'app数据管理';
$this->params['breadcrumbs'][] = $this->title;
?>
    <?php AppAsset::addScript($this, 'http://cdn.hcharts.cn/highcharts/highcharts.js');?>
    <div id="container" style="min-width:400px;height:400px;margin-bottom:50px;"></div>
    <div class="report-app-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => '序号',
//                 'headerOptions'=> ['width'=> '4%'],
            ],

            [
                'attribute' => 'referer',
                'value' => function($model){
                    return $model->referer;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'referer', ['H5'=>'H5','Android'=>'Android','IOS'=>'IOS'],
                    ['prompt'=>'所有']
                ),
                'contentOptions'=>['class'=>'referer']
            ],
            [
                'attribute' => 'register_num',
                'contentOptions'=>['class'=>'register_num']
            ],
            [
                'attribute' => 'banner_click',
                'contentOptions'=>['class'=>'banner_click']
            ],
            [
                'attribute' => 'banner_click_num',
                'contentOptions'=>['class'=>'banner_click_num']
            ],
            [
                'attribute' => 'lessons_num',
                'contentOptions'=>['class'=>'lessons_num']
            ],
            [
                'attribute' => 'evaluating_num',
                'contentOptions'=>['class'=>'evaluating_num']
            ],
            [
                'attribute' => 'article_num',
                'contentOptions'=>['class'=>'article_num']
            ],
            [
                'attribute' => 'product_num',
                'contentOptions'=>['class'=>'product_num']
            ],
            [
                'attribute' => 'date',
                'value' => function($model){
                    return "";
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_time','style'=>'width:80px']) ." -- ".Html::input('text', 'end_at', $date2, ['class' => 'required','id' => 'end_time','style'=>'width:80px']),
                'contentOptions'=>['class'=>'date']
            ]
        ],
    ]); ?>
</div>
<?php
$script = <<<JS
$(function () {
    var register_num = [$register_num];
    var banner_click = [$banner_click];
    var banner_click_num = [$banner_click_num];
    var lessons_num = [$lessons_num];
    var evaluating_num = [$evaluating_num];
    var article_num = [$article_num];
    var product_num = [$product_num];
    var xline = [$xline];
    
    var data_list = new Array();
    var data_list = [
        {
            name: '注册数',
            data: register_num,
        },
        {
            name: 'banner点击次数',
            data: banner_click
        },
        {
            name: 'banner点击人数',
            data: banner_click_num
        },        
        {
            name: '功课生成数',
            data: lessons_num
        },
        {
            name: '肤质测评参与人数',
            data: evaluating_num,
        },
        {
            name: '文章互动数（点赞+评论+分享）',
            data: article_num,
        },
        {
            name: '产品互动数（点赞+评论+分享）',
            data: product_num,
        },
        ];
    
    $('#container').highcharts({
        chart: {
            type: 'line'
        },
        title: {
            text: 'APP 数据'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: xline
        },
        yAxis: {
            title: {
                text: '数量 (个)'
            },
        },
        tooltip: {
            crosshairs: true,
            shared: true
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: true
            }
        },
        series: data_list
    });
});


$(function(){
     $('#start_time').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    });
    $('#end_time').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    }); 
    
    function totals(type){
        var num = 0;
        $("." + type).each(function(e){
            num = num + parseInt($(this).text());
        })
        return num;
    }
    
    var register_num = totals('register_num');
    var banner_click = totals('banner_click');
    var banner_click_num = totals('banner_click_num');
    var lessons_num = totals('lessons_num');
    var evaluating_num = totals('evaluating_num');
    var article_num = totals('article_num');
    var product_num = totals('product_num');
    
    $("tbody").prepend('<tr><td></td><td>总数据</td><td>'+ register_num +'</td><td>'+ banner_click +'</td><td>'+ banner_click_num +'</td><td>'+ lessons_num +'</td><td>'+ evaluating_num +'</td><td>'+ article_num +'</td><td>'+ product_num +'</td><td></td></tr>');

})

JS;
$this->registerJs($script);
?>