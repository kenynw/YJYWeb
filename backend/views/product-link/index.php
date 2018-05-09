<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductLinkSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '返利链接列表';
$this->params['breadcrumbs'][] = $this->title;

//搜索后展示时间
$date = yii::$app->getRequest()->get('add_time');

//是否筛选失败按钮,有才能批量操作
$change = '';
$status = yii::$app->getRequest()->get("ProductLinkSearch")['status'];
if ($status && $status == '2') {
    $change = Html::button("一键失败", ["class" => "btn btn-success update_alls_status",'style' => 'float:left;margin-left:20px;margin-top:-2px']);
}

?>
<div class="product-link-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'id' => 'grid-link',
        'showFooter' => true,
        'columns' => [
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                "header"=>'',
                'headerOptions' => ['width' => '30px'],
                'footer' => '<span style="float:left;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::button("批量删除", ["class" => "btn btn-danger delete_alls_link",'style' => 'float:left;margin-left:20px;margin-top:-2px']).$change,
                'footerOptions' => ['colspan' => 5],
                'contentOptions' => ['class'=>'checkboxs'],
            ],
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'product_id',
                'options' => ['width' => '5%'],
            ],
            [
                'label' => '产品名',
                'options' => ['width' => '20%'],
                'format' => 'raw',
                'value' => function($model){
                    $return = empty($model->productDetails->product_name) ? '' : Html::a($model->productDetails->product_name,['product-details/view','id'=>$model->product_id],['target'=>'_blank']);
                    return $return;
                },
            ],
            [
                'attribute' => 'tb_goods_id',
                'options' => ['width' => '10%'],
            ],
            [
                'attribute' => 'type',
                'format' => 'raw',
                'options' => ['width' => '8%'],
                'value' => function ($model) use ($linkType){
                    $return = '';
                    if (array_key_exists($model->type, $linkType)) {
                        $return = $linkType[$model->type];
                    }
                     return $return;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type',$linkType,
                    ['prompt' => '所有']),
            ],
            
            [
                'attribute' => 'url',
                'value' => function ($model) {
                    return empty($model->url) ? '-' : $model->url;
                },
                'options' => ['width' => '30%'],
                'contentOptions' => ['style' => 'word-break: break-all;max-width:50%'],
            ],
            [
                'attribute' => 'status',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value'     => function($model){     
                    $return = '';
                    if ($model->status > 0 && $model->url != '') {
                        $return = '成功';
                    } elseif ($model->status == 0 && $model->url == '') {
                        $return = "<button class='btn btn-xs btn-success btnstatus' data-status='0' data-type='status' data-id='".$model->id."'>失败</button>";
                    } elseif ($model->status > 0 && $model->url == '') {
                        $return = "<button class='btn btn-xs btn-success btnstatus' data-status='1' data-type='status' data-id='".$model->id."'>未转化</button>";
                    }
                    return $return;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'status', ['1'=>'成功','2'=>'失败','3'=>'未转化'],
                    ['prompt'=>'所有']
                ),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'admin_id',
                'value' => function ($model) {
                    return empty($model->admin->username) ? '' : $model->admin->username;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'admin_id', $adminList,
                    ['prompt'=>'所有']
                ),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'add_time',
                'format' => 'raw',
                'options' => ['width' => '200px'],
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->add_time);
                },
                'filter' => Html::input('text', "add_time", (!empty($date))?$date:'', ['class' => 'required','id' => 'add_time','style' => 'width:80px;']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'header' => '操作',
                'options'=>['style'=>'width:200px'],
                'footerOptions' => ['class'=>'hide']
            ]
        ],
    ]); ?>
</div>
<div id="sel_id" style="display:none"></div>
<?php
$script = <<<JS
//ajax修改页面状态  
$(document).on("click",'.btnstatus',function(){
	var id = $(this).attr("data-id");
	var status = $(this).attr("data-status");
    var type = $(this).attr("data-type");
    var url = '/product-link/change-status';
    var box = $(this);
    
	if(status == '1') {
        var btnval = '失败';
	} else {
        var btnval = '未转化';
	}
    
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data:{id:id,status:status,type:type},
        success : function(data) {
            if (data.status == "1") {
                if(status == 1){
                    var d = "<button class='btn btn-success btn-xs btnstatus' data-status='0' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
                }else{
                    var d = "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
                }
                box.parent("td").html(d);

				if(refresh){
					window.location.reload();
				}
                //alert('操作成功！');
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


// 时间搜索框
$('#add_time').datepicker({
    autoclose: true,
    format : 'yyyy-mm-dd',
    'language' : 'zh-CN',
});

//批量删除
$(document).on('click', '.delete_alls_link', function () { 
    var id = $('#grid-link').yiiGridView("getSelectedRows");
    if(id == ""){
        alert("请选择商品");
        return false;
    }
    
    if(confirm("确定要删除这些数据吗？")){
        $.ajax({
            url: '/product-link/delete-all',
            type: 'post',
            dataType: 'json',
            data:{id:id},
            success : function(data) {
                window.location.reload();
            },
            error : function(data) {
                alert('操作失败！');
            }
        });
    }
    
});
// $("#grid-link input[type='checkbox']").on('click',function(){
//     if ($("#grid-link input[type='checkbox']").is(':checked')) {
//         $('#grid-link .delete_alls_link').attr('disabled',false);
//         $('#grid-link .delete_alls_link').addClass('btn-danger');
//     } else {
//         $('#grid-link .delete_alls_link').attr('disabled','disabled');
//         $('#grid-link .delete_alls_link').removeClass('btn-danger');
//     }
// })
// $("#grid-link input[name='id_all']").on('click',function(){
//     $("#grid-link tbody input[type='checkbox']").each(function(){
//         if($(this).is(':checked')){
//             $('#grid-link .delete_alls_link').attr('disabled','disabled');
//             $('#grid-link .delete_alls_link').removeClass('btn-danger');
//         } else {
//             $('#grid-link .delete_alls_link').attr('disabled',false);
//             $('#grid-link .delete_alls_link').addClass('btn-danger');
//             return false;
//         }
//     })
// })
    
//批量转化（失败->未转化）
$(document).on('click', '.update_alls_status', function () { 
//     var id = $('#grid-link').yiiGridView("getSelectedRows");
//     if(id == ""){
//         alert("请选择商品");
//         return false;
//     }
    if(confirm("确定将所有转化‘失败’的链接状态改为‘未转化’吗？")){
        $.ajax({
            url: '/product-link/update-all',
            type: 'post',
            dataType: 'json',
            data:{},
            success : function(data) {
                window.location.reload();
            },
            error : function(data) {
                alert('操作失败！');
            }
        });
    }    
});

//记录全选
$(document).on("click",'#grid-link .select-on-check-all',function(){
    setTimeout(function(){
        check_list('#grid-link','#sel_id');
        setCookie('bottom-select',$('#sel_id').html()); 
    },100);
});
//记录多选
$(document).on("click",'#grid-link .checkboxs input',function(){
    check_list('#grid-link','#sel_id');
    setCookie('bottom-select',$('#sel_id').html()); 
});
$(function(){
    //翻页触发勾选
    var urlStr= window.location.href;
    if(urlStr.match('product-link/index.*page')){
        if(old = getCookie('bottom-select')){
            $("#sel_id").html(old);
            
            old = old.split("-");
            //处理已经选择的数据
            $("#grid-link input[name='id[]']").each(function(){
                if(inArray(old,parseInt($(this).val()) )){
                    $(this).attr("checked",true);
                }
            })
        }
    }else{
        $("#sel_id").html('');
    }
})
JS;
$this->registerJs($script);
?>