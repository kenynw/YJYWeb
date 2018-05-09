<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use common\models\HuodongSpecialDraw;
use common\functions\Tools;
use common\models\HuodongAddress;
use common\models\User;

/* @var $this yii\web\View */
/* @var $searchModel common\models\HuodongSpecialDrawSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '中奖地址管理';
$this->params['breadcrumbs'][] = $this->title;

$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');
?>
<div class="huodong-special-draw-index">
    <?php echo $this->render('_export'); ?>
    <p>
        <?php echo Html::button('获取最新数据', ['class' => 'btn btn-success btn-refresh']) ?>
    </p>
    <?php 
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => [
            'id' => 'grid',
            'class' => 'gridview',
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => '活动',
                'attribute' => 'hdid',
                'options' => ['width' => '25%'],
                'value'     => function($model){
                    return empty($model->huodongSpecialConfig->name) ? '' : $model->huodongSpecialConfig->name;
                },
                'filter' => Select2::widget([
                    'name' => 'HuodongSpecialDrawSearch[hdid]',
                    'data' => HuodongSpecialDraw::getHuodongNameArr(),
                    'options' => ['placeholder' => '请选择...'],
                    'initValueText' => $searchModel->hdid,
                    'value' => $searchModel->hdid,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ], 
            [
                'label' => '收货信息',
                'attribute' => 'information',
                'format' =>'raw',
                'value' => function ($model) {
                    if (!empty($model->huodongAddress->name) && !empty($model->huodongAddress->tel) && !empty($model->huodongAddress->address)) {
                        return Html::a(Tools::userTextDecode($model->huodongAddress->name), ['user/view','id'=> $model->huodongAddress->user_id], ['style' => 'color:green','title' => '真实用户','data-toggle'=>'tooltip','data-placement'=>'left'])."&nbsp;&nbsp;".$model->huodongAddress->tel."&nbsp;&nbsp;".$model->huodongAddress->address;
                    } else {
                        $user = User::findOne($model->uid);
                        if (!empty($user->username)) {
                            return Html::a(Tools::userTextDecode($user->username), ['user/view','id'=> $model->uid], ['style' => 'color:green','title' => '真实用户','data-toggle'=>'tooltip','data-placement'=>'left']);
                        } else {
                            return '该用户不存在';
                        }
                    }
                }
            ],
//             [
//                 'attribute' => 'username',
//                 'options' => ['width' => '25%']
//             ],
//             [
//                 'attribute' => 'prize',
//                 'value'     => function($model){
//                     return $model->prize.'元';
//                 },
//             ],  
//             [
//                 'attribute' => 'sendstatus',
//                 'value'     => function($model){
//                     return $model->sendstatus == 0 ? '未发放' : '已发放';
//                 },
//                 'filter' => Html::activeDropDownList($searchModel,
//                     'sendstatus', ['未发放','已发放'],
//                     ['prompt' => '所有']
//             )
//             ],
            [
                'attribute' => 'giftid',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->giftid == 1 && (empty($model->huodongAddress->name) && empty($model->huodongAddress->tel) && empty($model->huodongAddress->address))){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='giftid' data-id='".$model->id."'>是</button>";
                    }elseif ($model->giftid == 2){
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='giftid' data-id='".$model->id."'>无</button>";
                    } else {
                        return '是';
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'giftid',['0' => '无','1' => '是'],
                    ['prompt' => '所有'])
            ],
            [
                'attribute' =>  'addtime',
                'options' => ['width' => '13.5%'],
                'value' => function ($model) {
                    return empty($model->addtime) ? '' : date('Y-m-d H:i:s', $model->addtime);
                }
            ],
            [
                'attribute' =>  'endtime',
                'label' => '结束时间',
                'value' => function ($model) {
                    return empty($model->endtime) ? '' : date('Y-m-d H:i:s', $model->endtime);
                },
                'filter' => Html::input('text', 'start_at', (!empty($date1))?$date1:date('Y-m-d',time()), ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ." -- ".Html::input('text', 'end_at', $date2, ['class' => 'required','id' => 'end_at','style' => 'width:80px;']),
                'options' => ['width' => '13.5%'],
            ],          

//             [
//                 'class' => 'yii\grid\CheckboxColumn',
//                 'name' => 'id',
//                 'checkboxOptions' => function ($model, $key, $index, $column) {
//                       return [
//                           'id' => 'box'.$model->id,
//                           'send' => $model->sendstatus,
//                           'class' => 'boxColumn',
//                           'checked' => $model->sendstatus == 0 ? false :true,
//                           'key' => $model->id
//                       ];
//                 },
//                 'header' => '',
//                 'headerOptions' => ['width' => '']
//             ],
//             [
//                 'class' => 'yii\grid\ActionColumn',
//                 'options' => ['width' => '8%'],
//                 'template' => '{delete}',
//                 'header' => '操作',
//             ],
        ],
    ]); ?>
</div>
<?php
$script = <<<JS
$(function(){
    $(".boxColumn").on("click", function () {
        var id = $(this).attr("key");
        var send = $(this).attr('send');
         $.ajax({
            url: '/huodong-special-draw/update-sendstatus?id='+id,
            type: 'post',
            dataType: 'json',
            data:{send:send},
            success : function(data) {
                if (data.status == "0") {
                    alert('操作失败');
                }
                if (data.status == "1") {
//                     alert('操作成功！');
                    htm = data.sendstatus == 0 ? '未发放' : '已发放';
                    $("[data-key='"+id+"']").find("td").eq(3).html(htm);  
                    $("#box"+id).attr('send',data.sendstatus);
                }
            },
            error : function(data) {}
        });
    });
    
    $(".btn-refresh").click(function(){
        window.location.reload();
    })        
})

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

//修改状态
$(document).on("click",'.btnstatus',function(){
	var id = $(this).attr("data-id");
	var status = $(this).attr("data-status");
    var type = $(this).attr("data-type");
    var url = '/huodong-special-draw/change-status';
    var box = $(this);
    
	if(status == '0') {
        var btnval = '是';
	} else {
        var btnval = '无';
	}
    
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data:{id:id,status:status,type:type},
        success : function(data) {
            if (data.status == "1") {
                if(status == 1){
                    var d = "<button class='btn btn-xs btnstatus' data-status='0' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
                }else{
                    var d = "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
                }
                box.parent("td").html(d);
            }
        },
        beforeSend : function(data) {
            box.text("loading...");
        },
        error : function(data) {
            alert('操作失败！');
        }
    });
});
JS;
$this->registerJs($script);
?>