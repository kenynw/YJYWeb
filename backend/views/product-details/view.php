<?php

use yii\helpers\Html;
use kartik\tabs\TabsX;

/* @var $this yii\web\View */
/* @var $model common\models\ProductDetails */

$this->title = '产品详情页';
$this->params['breadcrumbs'][] = ['label' => '产品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$csrf = Yii::$app->request->csrfToken;
?>
<style>
	.ul ul {overflow: hidden;border: 1px solid #ddd;}
    .sortable li {float: left;min-height: 40px;text-align: center;}
    .table td {border-right:1px solid #ECF0F5}
    .table tr:nth-of-type(1) th {border-right:1px solid #ECF0F5}
</style>
<div class="product-details-view">
    <p>
        <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php //echo Html::a('Delete', ['delete', 'id' => $model->id], [
//             'class' => 'btn btn-danger',
//             'data' => [
//                 'confirm' => 'Are you sure you want to delete this item?',
//                 'method' => 'post',
//             ],
//         ]) ?>
    </p>

    <div>   
        <table class="table" style="border:1px solid #ECF0F5">
            <tr style="font-size: 20px;">
                <th colspan="2">基本信息</th>
                <th colspan="2">详细信息</th>
                <th colspan="2">备案信息</th>
            </tr>
            <tr>
                <td colspan="2"><label>产品图</label></td>
                <th width="150px">品牌</th><td width="20%"><?=empty($model->brand->name) ? (empty($model->brand->ename) ? '' : $model->brand->ename) : $model->brand['name'] ?></td>
                <th width="150px">备案号</th><td><?=$model->standard_number ?></td>
            </tr>
            <tr>
                <td rowspan="4" colspan="2" width="30%" style="text-align:center"><img src="<?= empty($model->product_img) ? Yii::$app->params['uploadsUrl'].'default-pc.jpg' : Yii::$app->params['uploadsUrl'].$model->product_img ?>" width="170" height="170" alt="" data-toggle="modal" data-target="<?= empty($model->product_img) ? '' : '#file' ?>"></td>
                <th>参考价</th><td><?=empty($model->price) ? '' : $model->price ?></td>
                <th>批准日期</th><td><?=empty($model->product_date) ? '' : date('Y-m-d',$model->product_date) ?></td>
            </tr>
            <tr>           
                <th>规格</th><td><?=$model->form ?></td>
                <th>生产国</th><td><?=$model->product_country ?></td>
            </tr>
            <tr>           
                <th>星级</th><td><?=$model->star ?></td>
                <th>生产企业（中）</th><td><?=$model->product_company ?></td>
            </tr>
            <tr>           
                <th>状态</th><td><?=empty($model->status) ? '下架' : '上架' ?></td>
                <th>生产企业（英）</th><td><?=$model->en_product_company ?></td>
            </tr>
            <tr>           
                <th>产品ID</th><td><?=$model->id ?></td>  
                <th>所属分类</th><td><?=empty($model->productCategory->cate_name) ? '未设置' : $model->productCategory->cate_name ?></td>
                <th></th><td></td>
            </tr>
            <tr>     
                <th>产品名</th><td><?=$model->product_name ?></td>      
                <th>是否推荐</th><td><?=empty($model->is_recommend) ? '默认' : '推荐' ?></td>
                <th></th><td></td>
            </tr>
            <tr>     
                <th>别名</th><td><?=$model->remark ?></td>      
                <th>是否上榜</th><td><?=empty($model->is_top) ? '默认' : '上榜' ?></td>
                <th></th><td></td>
            </tr>
        </table>
        
        <label>官方购买渠道</label>
        <div style="border:1px #ddd solid;width:100%;padding:10px;line-height:50px;word-break: break-all">
                淘宝：<?=$link['1']['link'] ?>&emsp;&emsp;&emsp;关联平台的商品id：<?=$link['1']['tb_goods_id'] ?><br>
                京东：   <?=$link['2']['link'] ?>&emsp;&emsp;&emsp;关联平台的商品id：<?=$link['2']['tb_goods_id'] ?><br>
                亚马逊：<?=$link['3']['link'] ?>&emsp;&emsp;&emsp;关联平台的商品id：<?=$link['3']['tb_goods_id'] ?>
        </div><br>
        
        <label>颜究院解读</label>  
            <div style="border:1px #ddd solid;width:100%;padding:10px;line-height:50px">
                <?=empty($model->product_explain) ? '无' : $model->product_explain; ?>
            </div><br>
        
        <label>产品功效</label>  
            <div style="border:1px #ddd solid;width:100%;padding:10px;line-height:50px">
                <?=empty($tagNameArr) ? '无' : join('，',$tagNameArr); ?>
            </div><br>
            
        <label>产品标签</label>  
            <div style="border:1px #ddd solid;width:100%;padding:10px;line-height:50px">
                <?=empty($tagNameArr2) ? '无' : join('，',$tagNameArr2); ?>
            </div><br>
        
        <label>含有成分</label>  
            <div style="border:1px #ddd solid;width:100%;padding:10px;line-height:50px">
                <?=empty($cateNameArr) ? '无' : join('，',$cateNameArr); 
//                     $arr = explode(",",$model->component_id);
//                     if ($arr[0] != '') {
//                         $stringArr = CommonFun::getConnectArr($arr, new ProductComponent(), 'id', 'name');
//                         $item = [];
//                         foreach ($stringArr as $key=>$val) {
//                             $item[$key]['content'] = "$val";
//                         }
//                         echo SortableInput::widget([
//                             'name'=> 'sort_list_2',
//                             'value'=>empty($model->component_id) ? '' : $model->component_id,
//                             'items' => $item,
//                             'hideInput' => true,
//                             'options' => ['class'=>'form-control', 'readonly'=>true,'id' => Yii::$app->controller->id.'-view'],
                        
//                         ]);
//                     }
                ?>
            </div>
     </div>
 
     <!-- 查看图片弹窗 -->
    <div class="modal fade" id="file">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">图片详情</h4>
                </div>
                <div class="file-modal-body" style="text-align:center">
                    <img src="" id="image" class="kv-preview-data file-preview-image file-zoom-detail" style="width: 50%; height: 50%;">
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <br>
    
<!-- 评论问答 -->
<?php
$items = [
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list">评论</span>',
        'content'=> $this->render('../comment/index', [
        'data_id' => $model->id,
        'type' => 1,
        'jump_url' => Yii::$app->request->url
        ]),
        'active'=>true
    ],
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list">问答</span>',
        'content'=> $this->render('../ask/index', [
            'data_id' => $model->id,
            'jump_url' => Yii::$app->request->url
        ]),
        'active'=>false
    ],
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list">操作记录</span>',
        'content'=> $this->render('/admin-log-view/index', [
            'relateId' => $model->id,
            'action' => Yii::$app->controller->id,
        ]),
        'active'=>false
    ],
];
echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'encodeLabels'=>false,
    'id' => 'view-tab'
]);
?>
</div>
<?php 
$script = <<<JS
$(function(){
//浏览图片
$('img').on('click', function(){
    $('#image').attr('src', this.getAttribute("src"));
});
    
//tab切换
$(document).on("click",'#view-tab li',function(){
    var index = $(this).index();
    setCookie('types',index,10000);
});

//评论数
if ( $("#view-tab-container .summary").length > 0 ) {
    if($("#view-tab-container #grid-comment .summary").length > 0){
        var num = $("#grid-comment .summary").find("b").eq(1).text();
        var content = "产品评论("+ num +")";
        $("#view-tab-container").find(".bar_list").eq(0).html(content);
    } else {
        var content = "产品评论(0)";
        $("#view-tab-container").find(".bar_list").eq(0).html(content);
    }
                
    if($("#view-tab-container #grid3 .summary").length > 0){
        var num = $("#view-tab-container #grid3 .summary").find("b").eq(1).text();
        var content = "问答("+ num +")";
        $("#view-tab-container").find(".bar_list").eq(1).html(content);
    } else {
        var content = "问答(0)";
        $("#view-tab-container").find(".bar_list").eq(1).html(content);
    }
                
} else {
    $("#view-tab-container").find(".bar_list").eq(0).html("产品点评(0)");
    $("#view-tab-container").find(".bar_list").eq(1).html("问答(0)");
}

//评论问答切换
var types=getCookie('types'); 
if(types == '1'){
    $("#view-tab").find("li").eq(1).attr("class","active");
    $("#view-tab-tab1").addClass("active");
    $("#view-tab-tab1").addClass("in");
                
    $("#view-tab").find("li").eq(0).attr("class","");  
    $("#view-tab-tab0").removeClass("active");
    $("#view-tab-tab0").removeClass("in");
    
    $("#view-tab").find("li").eq(2).attr("class","");
    $("#view-tab-tab2").removeClass("active");
    $("#view-tab-tab2").removeClass("in");
}else if(types == '2'){
    $("#view-tab").find("li").eq(2).attr("class","active");
    $("#view-tab-tab2").addClass("active");
    $("#view-tab-tab2").addClass("in");
    
    $("#view-tab").find("li").eq(1).attr("class","");
    $("#view-tab-tab1").removeClass("active");
    $("#view-tab-tab1").removeClass("in");
    
    $("#view-tab").find("li").eq(0).attr("class","");
    $("#view-tab-tab0").removeClass("active");
    $("#view-tab-tab0").removeClass("in");
}else{
    $("#view-tab").find("li").eq(0).attr("class","active");
    $("#view-tab-tab0").addClass("active");
    $("#view-tab-tab0").addClass("in");
    
    $("#view-tab").find("li").eq(1).attr("class","");
    $("#view-tab-tab1").removeClass("active");
    $("#view-tab-tab1").removeClass("in");
    
    $("#view-tab").find("li").eq(2).attr("class","");
    $("#view-tab-tab2").removeClass("active");
    $("#view-tab-tab2").removeClass("in");
}
});
 
$(function(){
    //定位到#view-tab
    var urlStr = window.location.href;
    if(!urlStr.match(/t$/) && (urlStr.match(/p=/) || urlStr.match(/page/) || urlStr.match(/CommentSearch/) || urlStr.match(/AskSearch/))){
        window.location.href = urlStr+'#view-tab';
    }
})
JS;
$this->registerJs($script);
?>