<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2;
use common\models\ProductCategory;
use kartik\rating\StarRating;
use yii\widgets\Pjax;
use common\models\ProductDetails;
use common\models\ProductLink;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductDetailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//搜索后展示时间
$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');

//添加品牌产品参数
$brand_id = empty(yii::$app->getRequest()->get('ProductDetailsSearch')['brand_id']) ? '0' : yii::$app->getRequest()->get('ProductDetailsSearch')['brand_id'];
$is_top = empty(yii::$app->getRequest()->get('ProductDetailsSearch')['is_top']) ? '0' : yii::$app->getRequest()->get('ProductDetailsSearch')['is_top'];

$this->title = '产品列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-details-index">
    <p>
        <?= empty(yii::$app->getRequest()->get('btype')) ? Html::a('添加产品', ['/product-details/create'], ['class' => 'btn btn-success']).'&nbsp;&nbsp;'.Html::a('批量添加', ['/product-details/grap'], ['class' => 'btn btn-info']).'&nbsp;&nbsp;'.Html::a('推广链接导入', ['/product-details/insert-excel'], ['class' => 'btn btn-info']).'&nbsp;&nbsp;'.Html::a('批量分类上架', ['/product-details/batch-cate'], ['class' => 'btn btn-info']) : Html::a('添加产品', 'javascript:void(0)', ['class' => 'btn btn-success brand-add','data-target' => '#brand-add', 'data-toggle'=>'modal']) ?>
    </p>




    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        "id" => "grid1",
        'showFooter' => true,
        'columns' => [
//             [
//                 'class' => 'yii\grid\SerialColumn',
//                 'header' => '序号',
//                 'headerOptions'=> ['width'=> '4%'],
//             ],
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                'headerOptions' => ['width' => '30px'],
                "header"=>'',
                'options' => ['width' => '2%'],
                'footer' => empty(yii::$app->getRequest()->get('btype')) ? '<span style="float:left"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::dropDownList('','',$cateList,["style" => "height:30px;float:left;margin-left:20px;margin-right:10px;",'prompt' => '修改分类','class' => 'bottom-update-select','data-type' => 'cate_id']).Html::dropDownList('','',['1'=>'上架','0'=>'下架'],["style" => "height:30px;float:left;margin-right:10px;",'class' => 'bottom-update-select','data-type' => 'status','prompt' => '状态设置']).Html::button('推荐',['class'=>'btn btn-success bottom-update-button','style' => 'float:left;margin-right:10px;margin-top:-2px','data-type' => 'is_recommend']).Html::button('上榜',['class'=>'btn btn-success bottom-update-button','style' => 'float:left;margin-right:10px;margin-top:-2px','data-type' => 'is_top']) : '<span style="float:left"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::button("批量移除", ["class" => "btn btn-danger delete_alls_brand_product",'style' => 'float:left;margin-left:20px;margin-top:-5px']),
                'footerOptions' => ['colspan' => 5],
                'contentOptions' => ['class'=>'checkboxs'],
//                 'visible' => empty(yii::$app->getRequest()->get('btype')) ? '0' : '1',
            ],
            [
                'attribute' => 'id',
                'options' => ['width' => '70px'],
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'product_name',
                'format' => 'raw',
                'options' => ['width' => '20%'],
                'value' => function($model){
                    return Html::a($model->product_name,['product-details/view','id'=>$model->id]);
                },
                'footerOptions' => ['class'=>'hide']
            ],
//            [
//                'attribute' => 'brand',
//                'options' => ['width' => '8%'],
//            ],
            [
                'attribute' => 'cate_id',
                'format' => 'raw',
                'value'     => function($model) use($cateList){
//                     return empty($model->productCategory->cate_name) ? '未设置' : $model->productCategory->cate_name;
                return Html::dropDownList('cate_item',$model->cate_id,$cateList);
                },
                'options' => ['width' => '5%'],
                'filter' => Html::activeDropDownList($searchModel,
                    'cate_id',$cateList,
                    ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'price',
                'options' => ['width' => '4%'],
                'value' => function($model) {
                    return empty($model->price) ? '' : '¥'.$model->price;
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'comment_num',
                'options' => ['width' => '4%'],
                'value' => function($model) {
                    return empty($model->comment_num) ? '-' : $model->comment_num;
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'askreply_num',
                'options' => ['width' => '4%'],
                'value' => function($model) {
//                     return empty(ProductDetails::getAskReplyNum($model->id)) ? '-' : ProductDetails::getAskReplyNum($model->id);
                    return empty($model->askreply_num) ? '-' : $model->askreply_num;
                },
                'footerOptions' => ['class'=>'hide']
            ],
//             [
//                 'attribute' => 'form',
//                 'headerOptions'=> ['width'=> '100px'],
//                 'value' => function($model) {
//                     return empty($model->form) ? '' : $model->form;
//                 },
//                 'footerOptions' => ['class'=>'hide']
//             ],
//             [
//                 'attribute' => 'star',
//                 'format' => 'raw',
//                 'options' => ['width' => '5%'],
//                 'value' => function($model){
//                     return $model->star.'星';
//                 },
//                 'filter' => Html::activeDropDownList($searchModel, 
//                     'star',['1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星',], 
//                     ['prompt' => '所有']),
//                 'footerOptions' => ['class'=>'hide']
//             ],


            [
                'attribute' => 'is_recommend',
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_recommend == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='is_recommend' data-id='".$model->id."'>已推荐</button>";
                    }else{
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='is_recommend' data-id='".$model->id."'>默认</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel, 
                    'is_recommend',['0' => '默认','1' => '已推荐'], 
                    ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'is_top',
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_top == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='is_top' data-id='".$model->id."'>已上榜</button>";
                    }else{
                        return empty($model->brand_id) ? '' : "<button class='btn btn-xs btnstatus' data-status='0' data-type='is_top' data-id='".$model->id."'>默认</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_top',['0' => '默认','1' => '已上榜'],
                    ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
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
                ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'has_img',
                'format' => 'raw',
                'options' => ['width' => '8%'],
                'value' => function($model){
                    return $model->has_img ? " 有图" : "-";
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'has_img',['0' => '无图','1' => '有图'],
                    ['prompt' => '所有']),
                    'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'is_link',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    $is_link = ProductLink::find()->where("product_id = $model->id")->count();
                    return empty($is_link) ? '-' : '有';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_link',['1' => '无','2' => '有'],
                ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'options' => ['width' => '15%'],
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;']),
                'footerOptions' => ['class'=>'hide']
//                 'filter' => '<div class="input-group input-medium date-picker input-daterange" data-date-format="yyyy-mm-dd">'.
//                             Html::input('text', 'start_at', $date1, ['class' => 'form-control','id' => 'start_at','style' => 'width:80px;padding-right:0;padding-left:0']).
//                             '<span class="input-group-addon">-</span>'.
//                             Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'form-control','id' => 'end_at','style' => 'width:80px;padding-right:0;padding-left:0']).
//                             '</div>'
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} &nbsp;&nbsp; {delete}{remove}',
                'header' => '操作',
                'options' => ['width' => '8%'],
                'footerOptions' => ['class'=>'hide'],
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        $options = [
                            'title' => Yii::t('yii', '编辑'),
                            'class' => 'glyphicon glyphicon-pencil',
                        ];
                        return empty(yii::$app->getRequest()->get('btype')) ? Html::a('',['product-details/update','id' => $model->id],$options) : '';
                    },
                    'delete' => function ($url, $model, $key) {
                        $options = [
                            'title' => Yii::t('yii', '删除'),
                            'data-method' => 'post',        
                            'data-confirm' => '您确定要删除此项吗？',
                            'data-pjax' => '0'
                        ];
                        return empty(yii::$app->getRequest()->get('btype')) ? Html::a('<span class="glyphicon glyphicon-trash" target=""></span>',['product-details/delete','id' => $model->id,'url' => Yii::$app->request->url],$options) : '';          
                    },
                    'remove' => function ($url, $model, $key) {
                    $options = [
                        'title' => Yii::t('yii', '移除'),
                        'data-method' => 'post',
                        'data-confirm' => '您确定要移除此项吗？',
                        'data-pjax' => '0'
                    ];
                        return empty(yii::$app->getRequest()->get('btype')) ? '' : (empty(yii::$app->getRequest()->get('ProductDetailsSearch')['is_top']) ? Html::a('移除',['product-details/brand-remove','id' => $model->id,'brand_id' => $model->brand_id],$options) : Html::a('移除',['product-details/brand-remove','id' => $model->id,'brand_id' => $model->brand_id,'is_top' => '1'],$options));
                    },
                ],
            ]
        ],
        'pager' => [
            'firstPageLabel' => "首页",
            'prevPageLabel' => '<<',
            'nextPageLabel' => '>>',
            'lastPageLabel' => '末页',
        ],
    ]); ?>
</div>

<!--品牌添加产品弹框-->
<div class="modal fade bs-example-modal-lg" id="brand-add">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">产品列表</h4>
            </div>
            <div class="modal-body">
                <?php Pjax::begin([
                    'enablePushState' => false,
                    'timeout'         => 10000,
                ])?>
                <div class="brand-product">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider2,
                        'filterModel' => $searchModel2,
                        "id" => "grid2",
                        'columns' => [
                            [
                                "class" => 'yii\grid\CheckboxColumn',
                                "name" => "id",
                                "header"=>'<span style="display:;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>',
                                'headerOptions' => ['width' => '8%'],
                                'contentOptions' => ['class'=>'checkboxs'],
                                'checkboxOptions' => function ($model, $key, $index, $column) {
                                    if (!empty(yii::$app->getRequest()->get('btype'))) {
                                        $brand_id = empty(yii::$app->getRequest()->get('ProductDetailsSearch')['brand_id']) ? '0' : yii::$app->getRequest()->get('ProductDetailsSearch')['brand_id'];
                                        $is_top = empty(yii::$app->getRequest()->get('ProductDetailsSearch')['is_top']) ? '0' : yii::$app->getRequest()->get('ProductDetailsSearch')['is_top'];
                                        $is_exsit = ProductDetails::find()->select('id')->where("brand_id = $brand_id AND id = $model->id")->all();
                                        if (!empty($is_exsit)) {
                                            return ['disabled' => 'disabled'];
                                        }
                                    }
                                }                                
                            ],
                            [
                                'attribute' => 'id',
                                'options' => ['width' => '12%']
                            ],
                            [
                                'attribute' => 'product_name',
                                'format' => 'raw',
                                'value' => function($model){
                                    return Html::a($model->product_name,['product-details/view','id'=>$model->id],['data-pjax'=>'false','target'=>'_blank']);
                                }
                            ],
//                            'brand',
                            [
                                'attribute' => 'cate_id',
                                'value'     => function($model){
                                    return empty($model->productCategory->cate_name) ? '未设置' : $model->productCategory->cate_name;
                                },
                                'filter' => Html::activeDropDownList($searchModel2,
                                    'cate_id',$cateList,
                                    ['prompt' => '所有'])
                            ],
                            [
                                'attribute' => 'price',
                                'options' => ['style' => 'width:70px'],
                                'value' => function($model) {
                                    return empty($model->price) ? '' : $model->price;
                                }
                            ],
                            [
                                'attribute' => 'form',
                                'value' => function($model) {
                                    return empty($model->form) ? '' : $model->form;
                                }
                            ],
                            [
                                'attribute' => 'star',
                                'format' => 'raw',
                                'value' => function($model){
                                    $stars = '';
                                    for($i=0;$i<$model->star;$i++){
                                        $stars .= "<span class='star-active-icon'></span>";
                                    }
                                    if ($model->star < 5) {
                                        for($i=0;$i<5-($model->star);$i++){
                                            $stars .= "<span class='star-icon'></span>";
                                        }
                                    }
                                    return $stars;
                                },
                                'filter' => Html::activeDropDownList($searchModel2,
                                    'star',['1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星',],
                                    ['prompt' => '所有'])
                            ],
//                             [
//                                 'attribute' => 'status',
//                                 'format' => 'raw',
//                                 'value' => function($model){
//                                     if($model->status == 1){
//                                         return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='status' data-id='".$model->id."'>已上架</button>";
//                                     }else{
//                                         return "<button class='btn btn-xs btnstatus' data-status='0' data-type='status' data-id='".$model->id."'>已下架</button>";
//                                     }
//                                 },
//                                 'filter' => Html::activeDropDownList($searchModel2,
//                                     'status',['0' => '已下架','1' => '已上架'],
//                                     ['prompt' => '所有'])
//                             ],
                        ],
                    ]); ?>
                </div>
                <?php Pjax::end()?>

                <div style="width:320px;height:120px;">
                    <div style="clear:both;">
                        <?= Html::a("确认添加", "javascript:void(0);", ["class" => "btn btn-success select-product"]) ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span id="product_num"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- 品牌添加产品页-添加产品弹窗 -->
<div id="sel_id" style="display: none;"></div>
<!-- 产品列表页-批量上架 --><!-- 品牌添加产品页-批量移除 -->
<div id="sel_id2" style="display: none;"></div>
<?php

$script = <<<JS
$(function(){  
//时间搜索框
// $(".date-picker").datepicker({
//     language: "zh-CN",
//     autoclose: true,
// });
    // 时间搜索框
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
    
   //判断是否修改过分类或上下架，触发多选（产品列表页，批量上架后当前页面复选框仍记录批量上架前的复选框所勾选过的项）
   var bottom_update_select =  getCookie('bottom-update-select');
   if(bottom_update_select){
       bottom_update_select = bottom_update_select.split("-");
       //处理已经选择的数据
       $("#grid1 input[name='id[]']").each(function(){
           if(inArray(bottom_update_select,parseInt($(this).val()) )){
               $(this).attr("checked",true);
           }
       })
   }
});
    
//ajax修改页面状态  
status_ajax("/product-details/change-status");

//单项修改分类
$(document).on('change',"#grid1 select[name='cate_item']",function(){
    var cate_id = $(this).val();
    var id = $(this).parent().parent().attr('data-key');

    $.ajax({
        url: '/product-details/update-cate',
        type: 'post',
        dataType: 'json',
        data:{id:id,cate_id:cate_id},
        success : function(data) {
            if (data.status == '1') {
                art.dialog({content:'修改成功',icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            } else {
                alert('操作失败！');
            }
        },
    });
})

//批量修改
function bottomUpdate(id,type,type_id) {
    var url = window.location.href;
    $.ajax({
    url: '/product-details/bottom-update',
    type: 'post',
    dataType: 'json',
    data:{id:id,type:type,type_id:type_id},
    success : function(data) {console.log(data);
        if (data.status == '1') {
            art.dialog({content:'修改成功',icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            //修改过分类或上下架，记录
            check_list('#grid1','#sel_id2');
            setCookie('bottom-update-select',$('#sel_id2').html(),0.08);
            window.location.href = url;
        } else {
            alert('操作失败！');
        }
    },
})
}
$(document).on('click','.bottom-update-button',function(){
var id = $("#grid1").yiiGridView("getSelectedRows");
var type = $(this).attr('data-type');
var type_id = $(this).val();
if(id == ""){
    alert("请选择商品");
    return false;
}
bottomUpdate(id,type,type_id);
});
$(document).on('change','.bottom-update-select',function(){
var id = $("#grid1").yiiGridView("getSelectedRows");
var type = $(this).attr('data-type');
var type_id = $(this).val();
if(id == ""){
    $(this).val('');
    alert("请选择商品");
    return false;
}
bottomUpdate(id,type,type_id);
});

//记录全选
$(document).on("click",'#grid1 .select-on-check-all',function(){
    setTimeout(function(){
        //产品列表页，批量上架后当前页面复选框仍记录批量上架前的复选框所勾选过的项
        check_list('#grid1','#sel_id2');
        //品牌产品页，批量移除复选框所勾选过的项
        setCookie('bottom-remove-select',$('#sel_id2').html()); 
    },100);
});
//记录多选
$(document).on("click",'#grid1 .checkboxs input',function(){
    //产品列表页，批量上架后当前页面复选框仍记录批量上架前的复选框所勾选过的项
    check_list('#grid1','#sel_id2');
    //品牌产品页，批量移除复选框所勾选过的项
    setCookie('bottom-remove-select',$('#sel_id2').html()); 
});

//品牌产品页--------------------------------------------------------
//点击添加产品，定位到之前的url
$(".brand-add").on('click', function() {
    var brand_id = "$brand_id";
    var is_top = "$is_top";
    var url = getCookie('brand_url_' + brand_id + '_' + is_top);
    console.log(url);
    if(url != 'undefined'){
        var options = {
            url: url,
            replace:false,
            timeout:0
        }
        $.pjax.reload('#w0', options);
    } else {
        $.pjax.reload('#w0', {timeout:0});
    }
});
//记录全选
$(document).on("click",'#w0 .select-on-check-all',function(){
    setTimeout(function(){
        check_list('#w0','#sel_id');
    },100);
});
//记录多选
$(document).on("click",'#w0 .checkboxs input',function(){
    check_list('#w0','#sel_id');
});

//关闭弹框，刷新页面
$("#brand-add").on("hide.bs.modal",function(){
    window.location.reload();
});

//pjax请求之前调用
$("#w0").on('pjax:send', function() {
    check_list('#w0','#sel_id');
});

//pjax请求成功之后调用
$("#w0").on('pjax:complete', function(e) {
    if(e.relatedTarget){
        //记录请求url
        var url = e.relatedTarget.href;
        var brand_id = "$brand_id";
        var is_top = "$is_top";
        setCookie('brand_url_' + brand_id + '_' + is_top,url,1800);
    }
    
   var old =  $("#sel_id").html();
   if(old){
       old = old.split("-");
       //处理已经选择的数据
       $("#w0 input[name='id[]']").each(function(){
           if(inArray(old,parseInt($(this).val()) )){
               $(this).attr("checked",true);
           }
       })
   }
});

//品牌添加产品
$(document).on("click",'.select-product',function(){
    //var id = $("#grid2").yiiGridView("getSelectedRows");

    var id =  $("#sel_id").html();
    id = id.split("-");

    if(id == ""){
        alert("请选择商品");
        return false;
    }

     $.ajax({
        url: '/product-details/brand-add-product',
        type: 'post',
        dataType: 'json',
        data:{id:id,brand_id:$brand_id,is_top:$is_top},
        success : function(data) {
            //排除已经添加的产品
            var brand_id = "$brand_id";
            var is_top = "$is_top";
            var url = getCookie('brand_url_' + brand_id + '_' + is_top);
            if(url != 'undefined'){
                var options = {url: url,replace:false,timeout:0}
                $.pjax.reload('#w0', options);
            } else {
                $.pjax.reload('#w0',{timeout:0});
            }
            $("#sel_id").html("");
            $("#product_num").html("");
            
            art.dialog({content:'<span style="font-weight:bold;">成功添加' + id.length + '个产品<span>',icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            
        },
        error : function(data) {
            alert('操作失败！');
        }
    });
    
});

//批量移除
// $("#grid1 input[type='checkbox']").on('click',function(){
//     if ($("#grid1 input[type='checkbox']").is(':checked')) {
//         $('#grid1 .delete_alls_brand_product').attr('disabled',false);
//         $('#grid1 .delete_alls_brand_product').addClass('btn-danger');
//     } else {
//         $('#grid1 .delete_alls_brand_product').attr('disabled','disabled');
//         $('#grid1 .delete_alls_brand_product').removeClass('btn-danger');
//     }
// })
// $("#grid1 input[name='id_all']").on('click',function(){
//     $("#grid1 tbody input[type='checkbox']").each(function(){
//         if($(this).is(':checked')){
//             $('#grid1 .delete_alls_brand_product').attr('disabled','disabled');
//             $('#grid1 .delete_alls_brand_product').removeClass('btn-danger');
//         } else {
//             $('#grid1 .delete_alls_brand_product').attr('disabled',false);
//             $('#grid1 .delete_alls_brand_product').addClass('btn-danger');
//             return false;
//         }
//     })
// })
//品牌产品页记录复选值
$(function(){
    //如果是不是初始化状态的品牌产品页，触发勾选
    if($('#grid1').find('.delete_alls_brand_product')){
        var urlStr= window.location.href;
        if(urlStr.match('brand$')){
            $("#sel_id2").html('');
        }else{
            if(old = getCookie('bottom-remove-select')){
                $("#sel_id2").html(old);
                
                old = old.split("-");
                //处理已经选择的数据
                $("#grid1 input[name='id[]']").each(function(){
                    if(inArray(old,parseInt($(this).val()) )){
                        $(this).attr("checked",true);
                    }
                })
            }
        }
    }
})
$(document).on("click",'.delete_alls_brand_product',function(){
//     var id = $("#grid1").yiiGridView("getSelectedRows");
    var id =  $("#sel_id2").html();
    id = id.split("-");

    if(id == ""){
        alert("请选择商品");
        return false;
    }
    $.ajax({
        url: '/product-details/brand-remove-all',
        type: 'post',
        dataType: 'json',
        data:{id:id,brand_id:$brand_id,is_top:$is_top},
    });   
});


JS;
$this->registerJs($script);
?>
