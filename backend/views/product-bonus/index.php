<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductBonusSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '商城列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-bonus-index">
    <?= $this->render('_import', ['model' => $searchModel]); ?>
    <p>
        <?php //echo Html::a('添加商品','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
        <?= Html::a('添加商品',['create'], ['class' => 'btn btn-success']) ?>
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
        'showFooter' => true,
        'id' => 'grid-bonus',
        'columns' => [
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                "header"=>'',
                'headerOptions' => ['width' => '30px'],
                'footer' => '<span style="float:left;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::button("批量删除", ["class" => "btn delete_alls_bonus",'style' => 'float:left;margin-left:20px;margin-top:-2px','disabled' => 'disabled']).Html::dropDownList('','',['1'=>'上架','0'=>'下架'],["style" => "height:30px;float:left;margin-left:10px;",'class' => 'bottom-update-select','data-type' => 'status','prompt' => '状态设置']),
                'footerOptions' => ['colspan' => 5],
            ],
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'product_id',
                'options' => ['width' => '5%'],
                'format' => 'raw',
                'value' => function ($model) {
                    return empty($model->product_id) ? '' : Html::a($model->product_id, ['product-details/view','id'=> $model->product_id]);
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'goods_id',
                'value' => function ($model) {
                    return $model->goods_id;
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'type',
                'label' => '有无优惠券',
                'format' => 'raw',
                'value' => function ($model) {
                    empty($model->bonus_link) ? $data = '' :
                    $data = '';
                    if(date('Y-m-d') > $model->end_date){
                        $data = '<br/><span class="label label-danger">已过期</span>';
                    }
                    
                    return empty($model->bonus_link) ? Html::a('商品链接',$model->goods_link) : Html::a('优惠券链接',$model->bonus_link).$data;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type',['1' => '有','2' => '无'],
                    ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'price',
                'value' => function ($model) {
                    return empty(intval($model->price)) ? '' : $model->price.'元';
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'status',
                'options' => ['width' => '8%'],
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
                    ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'data_type',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    return $model->product_id ? '自动跑' : '手动添加';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'data_type',['1' => '手动添加','2' => '自动跑'],
                    ['prompt' => '所有']),
                    'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'sort',
                'value' => function($model){
                    return $model->sort;
                },
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->updated_at);
                },
                'footerOptions' => ['class'=>'hide']
                //                 'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;'])
            ],
            
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
                'header' => '操作',
                'options' => ['width' => '5%'],
//                 'buttons' => [                 
//                     'update' => function ($url, $model, $key) {
//                         return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
//                             'data-toggle' => 'modal',
//                             'data-target' => '#update-modal',
//                             'class' => 'data-update',
//                             'data-id' => $key,
//                         ]);
//                     },
//                 ]
            ],
        ],
    ]); ?>
</div>
<?php 
$script = <<<JS
$(function(){
//清空modal
// $('.modal').on('hidden.bs.modal', function () {
//     $(".modal-body").empty();
// })  
//创建修改modal
//  $('.data-update').on('click', function () {
//     var id = $(this).attr("data-id");
//     if (id == '') {
//         $('.modal-title').html("添加");
//         var url = '/product-bonus/create';
//     } else {
//         $('.modal-title').html("编辑");
//         var url = '/product-bonus/update';
//     }
//     $.get(url, { id: id },
//         function (data) {
//             $('.modal-body').html(data);
//         }  
//     );
// });
    
//ajax修改页面状态  
status_ajax("/product-bonus/change-status");
})

//批量删除
$(document).on('click', '.delete_alls_bonus', function () { 
    var id = $('#grid-bonus').yiiGridView("getSelectedRows");
    if(confirm("确定要删除这些数据吗？")){
        $.ajax({
            url: '/product-bonus/delete-all',
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
$("#grid-bonus input[type='checkbox']").on('click',function(){
    if ($("#grid-bonus input[type='checkbox']").is(':checked')) {
        $('#grid-bonus .delete_alls_bonus').attr('disabled',false);
        $('#grid-bonus .delete_alls_bonus').addClass('btn-danger');
    } else {
        $('#grid-bonus .delete_alls_bonus').attr('disabled','disabled');
        $('#grid-bonus .delete_alls_bonus').removeClass('btn-danger');
    }
})
$("#grid-bonus input[name='id_all']").on('click',function(){
    $("#grid-bonus tbody input[type='checkbox']").each(function(){
        if($(this).is(':checked')){
            $('#grid-bonus .delete_alls_bonus').attr('disabled','disabled');
            $('#grid-bonus .delete_alls_bonus').removeClass('btn-danger');
        } else {
            $('#grid-bonus .delete_alls_bonus').attr('disabled',false);
            $('#grid-bonus .delete_alls_bonus').addClass('btn-danger');
            return false;
        }
    })
})
//批量修改
function bottomUpdate(id,type,type_id) {
    var url = window.location.href;
    $.ajax({
    url: '/product-bonus/bottom-update',
    type: 'post',
    dataType: 'json',
    data:{id:id,type:type,type_id:type_id},
    success : function(data) {
        if (data.status == '1') {
            art.dialog({content:'修改成功',icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            window.location.href = url;
        } else {
            alert('操作失败！');
        }
    },
})
}
$(document).on('change','.bottom-update-select',function(){
var id = $("#grid-bonus").yiiGridView("getSelectedRows");
var type = $(this).attr('data-type');
var type_id = $(this).val();
if(id == ""){
    $(this).val('');
    alert("请选择商品");
    return false;
}
bottomUpdate(id,type,type_id);
});
JS;
$this->registerJs($script);
?>