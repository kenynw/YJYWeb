<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use common\models\ProductCategory;
use backend\assets\AppAsset;
AppAsset::register($this);
use common\models\ProductDetails;

/* @var $this yii\web\View */
/* @var $model common\models\Ranking */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => '分类排行榜设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

//分类
$cateid = ProductCategory::find()->asArray()->all();
$cateList = [];
// $cateList['0'] = '未设置';
foreach ($cateid as $key=>$val) {
    $cateList[$val['id']] = $val['cate_name'];
}

$rank_id = empty(yii::$app->getRequest()->get('id')) ? '0' : yii::$app->getRequest()->get('id');
?>
<div class="ranking-view">

    <p>
        <?= Html::a('添加产品', 'javascript:void(0)', ['class' => 'btn btn-success rank-add','data-target' => '#rank-add', 'data-toggle'=>'modal']) ?>
    </p>

    <div class='example'>
    <div class='gridly'>

    <?php 
        if (!empty($productList)) {
            foreach ($productList as $key=>$val) { ?>
            <div class='brick small'>

            <a class='delete' href='#'>&times;</a>
                <?php 
                    $product = ProductDetails::findOne($val['product_id']);
                    echo empty($product) ? "该商品不存在" : $product->product_name;
                ?>
            </div>    
    <?php } 
        }?>   
     
    </div>
    </div>
    
<!--弹窗 -->
    <div class="modal fade bs-example-modal-lg" id="rank-add">
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
                    <div class="rank-product">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
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
//                                             $brand_id = empty(yii::$app->getRequest()->get('ProductDetailsSearch')['brand_id']) ? '0' : yii::$app->getRequest()->get('ProductDetailsSearch')['brand_id'];
//                                             $is_top = empty(yii::$app->getRequest()->get('ProductDetailsSearch')['is_top']) ? '0' : yii::$app->getRequest()->get('ProductDetailsSearch')['is_top'];
//                                             $is_exsit = ProductDetails::find()->select('id')->where("brand_id = $brand_id AND id = $model->id")->all();
//                                             if (!empty($is_exsit)) {
//                                                 return ['disabled' => 'disabled'];
//                                             }
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
                                [
                                    'attribute' => 'cate_id',
                                    'value'     => function($model){
                                        return empty($model->productCategory->cate_name) ? '未设置' : $model->productCategory->cate_name;
                                    },
                                    'filter' => Html::activeDropDownList($searchModel,
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
                                    'filter' => Html::activeDropDownList($searchModel,
                                        'star',['1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星',],
                                        ['prompt' => '所有'])
                                ],
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
    <div id="sel_id" style="display: ;"></div>

</div>

<?php
$script = <<<JS
    
//关闭弹框，刷新页面
$("#rank-add").on("hide.bs.modal",function(){
    window.location.reload();
});
    
//记住多选
function check_list(){    
    var news = [];
    var dif =[];    
    $("#w0 input[name='id[]']").each(function(){
        if($(this).is(':checked')){
            news.push($(this).val());   
        }else{
            dif.push($(this).val());
        }
    });
    
    var old =  $("#sel_id").html();
    
    if(old){
        old = old.split("-");
        //添加新数据
        for( i= 0; i< news.length; i++){ 
            if(!inArray(old, parseInt(news[i]) )){
                old.push(parseInt(news[i]));
            }
        }
        
        //删除旧数据
        for( i= 0; i< dif.length; i++){ 
            if(j = inArray(old, parseInt(dif[i]) )){
                old.splice(j-1,1);
            }
        }
    }else{
        old = news;
    }
    
    console.log(old);
    if(old){
        var num = old.length;
        if(num){
            $("#product_num").html("已选择<span style='color:red;font-weight:bold'>" + num + "</span>个产品");
        }else{
            $("#product_num").html("");
        }

        old = old.join("-");
        $("#sel_id").html(old);
    }
}
$(document).on("click",'#w0 .select-on-check-all',function(){
    setTimeout(function(){
        check_list();
    },100);
});
$(document).on("click",'#w0 .checkboxs input',function(){
    check_list();
});
//pjax请求成功之后调用
$("#w0").on('pjax:complete', function(e) {
    if(e.relatedTarget){
        //记录请求url
        var url = e.relatedTarget.href;
        var rank_id = "$rank_id";
        setCookie('rank_url_' + rank_id,url,1800);
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
    
//添加产品
$(document).on("click",'.select-product',function(){
    var id =  $("#sel_id").html();
    id = id.split("-");
                    
    //每个排行榜的产品数，最少为3个，最多为10个                
    var exsit_ids = $('.gridly').children('.brick').length;                       

    if(id == ""){
        alert("请选择商品");
        return false;
    } if((exsit_ids + id.length < 3) || (exsit_ids + id.length > 10)){
        alert("每个排行榜的产品数，最少为3个，最多为10个");
        return false;
    }

     $.ajax({
        url: '/ranking/add-product?id=$rank_id',
        type: 'post',
        dataType: 'json',
        data:{id:id},
        success : function(data) {
            //排除已经添加的产品
//             var rank_id = "";
//             var url = getCookie('rank_url_' + rank_id);
//             if(url != 'undefined'){
//                 var options = {url: url,replace:false,timeout:0}
//                 $.pjax.reload('#w0', options);
//             } else {
//                 $.pjax.reload('#w0',{timeout:0});
//             }
            $("#sel_id").html("");
            $("#product_num").html("");
            
            art.dialog({content:'<span style="font-weight:bold;">成功添加' + id.length + '个产品<span>',icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            window.location.reload();
            
        },
        error : function(data) {
            alert('操作失败！');
        }
    });
    
});
    
  
JS;
$this->registerJs($script);
?>