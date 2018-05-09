<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\ProductCategory;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\base\Object;
use backend\models\CommonFun;
use common\models\ProductDetails;

/* @var $this yii\web\View */
/* @var $model common\models\Ranking */
/* @var $form yii\widgets\ActiveForm */
?>

<style>
/* 分类排行榜产品tag样式 */
/* tag */
.default-tag a,.default-tag a span,.plus-tag a,.plus-tag a em,.plus-tag-add a{background:url(../images/tagbg.png) no-repeat;}
.tagbtn a{ width:120px; height:70px; padding:7px 10px 0 5px; overflow:hidden;zoom:1; background:#B2DAF4; margin:0 10px 10px 0; border:1px solid #e8e8e8; cursor:move;box-shadow: 0px 0px 10px #505050;  color:black; float:left;overflow:hidden;}
/* plus-tag */
.plus-tag{padding:0 0 10px 0;}
.plus-tag a{background-position:100% -26px;}
.plus-tag a span{float:left; width:110px; height:60px;text-overflow: -o-ellipsis-lastline;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;}
.plus-tag a em{display:block;float:left;margin:-6px 15px 15px 100px;width:13px;height:13px;overflow:hidden;background-position:-165px -100px;cursor:pointer;position:absolute}
.plus-tag a:hover em{background-position:-168px -64px;}
/* plus-tag-add */
.plus-tag-add{float:left;}
.plus-tag-add div{float:left;margin-right:15px;}
.plus-tag-add button{float:left;}
</style>
<div class="ranking-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput() ?>
    
    <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架']) ?>

    <?php //echo $form->field($model, 'category_id')->dropDownList($cateList,['prompt'=>'--- 请选择 ---']) ?>
    
    <?= $form->field($model, 'banner')->widget('common\widgets\file_upload\FileUpload',[
        'config'=>[
            'domain_url' => Yii::$app->params['uploadsUrl'],
            'explain' => '<b>推荐尺寸：</b>672*423',
        ],
    ]) ?>
    
    <label class="control-label" for="ranking-category_id">添加榜单产品(已上架)</label><br>
    <div class="plus-tag-add" style="float:left;margin-bottom:10px;width:100%;">
        <?php
        echo Select2::widget([
            'name' => 'productSelect',
            'options' => ['placeholder' => '请输入产品名称 ...'],
            'pluginOptions' => [
               'placeholder' => 'Waiting...',
               'language'=>"zh-CN",
               'minimumInputLength'=> 1,
               'allowClear' => true,
               'ajax' => [
                   'url' => '/ranking/search-product',
                   'dataType' => 'json',
                   'cache' => true,
                   'data' => new JsExpression('function(params) { return {q:params.term}; }'),
               ],
               'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
               'templateResult' => new JsExpression('function(res) { return res.text; }'),
               'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
            'pluginEvents' => [
                "select2:select" => "function(){
                    //不能超过10个
                    product_len = $('.plus-tag a').length;
                    if(product_len >= 10){
                        $('.product-error').html('榜单产品不能超过10个');
                        return false;
                    } else {
                        $(':submit').attr('disabled', false).removeClass('disabled');
                        $('.product-error').html('');
                    }
                
                    //生成标签样式
                    var name = $(this).next().find('.select2-selection__rendered').attr('title');
                    id = $(this).val();                  
                    setTips(name, id);                    
                    $('#w1').select2('open'); 
                
                    //排序
                    var uplen = $('.success').attr('data-uplen');
                    if (($('.plus-tag a').length == '1' && $('.success').html() == '确认添加') || ($('.plus-tag a').length == uplen && $('.success').html() == '保存')) {
                        (function($) {
                        var dragging, placeholders = $();
                        $.fn.sortable = function(options) {
                        	options = options || {};
                        	return this.each(function() {
                        		if (/^enable|disable|destroy$/.test(options)) {
                        			var items = $(this).children($(this).data('items')).attr('draggable', options == 'enable');
                        			options == 'destroy' &&	items.add(this)
                        				.removeData('connectWith').removeData('items')
                        				.unbind('dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s');
                        			return;
                        		}
                        		var index, items = $(this).children(options.items), connectWith = options.connectWith || false;
                        		
                        		if(items.length==0){return false;}
                        		
                        		var placeholder = $('<' + items[0].tagName + ' class=\"sortable-placeholder\">');
                        		var handle = options.handle, isHandle;
                        		items.find(handle).mousedown(function() {
                        			isHandle = true;
                        		}).mouseup(function() {
                        			isHandle = false;
                        		});
                        		$(this).data('items', options.items)
                        		placeholders = placeholders.add(placeholder);
                        		if (connectWith) {
                        			$(connectWith).add(this).data('connectWith', connectWith);
                        		}
                        		items.attr('draggable', 'true').bind('dragstart.h5s', function(e) {
                        			if (handle && !isHandle) {
                        				return false;
                        			}
                        			isHandle = false;
                        			var dt = e.originalEvent.dataTransfer;
                        			dt.effectAllowed = 'move';
                        			dt.setData('Text', 'dummy');
                        			dragging = $(this).addClass('sortable-dragging');
                        			index = dragging.index();
                        		}).bind('dragend.h5s', function() {
                        			dragging.removeClass('sortable-dragging').fadeIn();
                        			placeholders.detach();
                        			if (index != dragging.index()) {
                        				items.parent().trigger('sortupdate');
                        			}
                        			dragging = null;
                        		}).not('a[href], img').bind('selectstart.h5s', function() {
                        			this.dragDrop && this.dragDrop();
                        			return false;
                        		}).end().add([this, placeholder]).bind('dragover.h5s dragenter.h5s drop.h5s', function(e) {
                        			if (!items.is(dragging) && connectWith !== $(dragging).parent().data('connectWith')) {
                        				return true;
                        			}
                        			if (e.type == 'drop') {
                        				e.stopPropagation();
                        				placeholders.filter(':visible').after(dragging);
                        				return false;
                        			}
                        			e.preventDefault();
                        			e.originalEvent.dataTransfer.dropEffect = 'move';
                        			if (items.is(this)) {
                        				dragging.hide();
                        				$(this)[placeholder.index() < $(this).index() ? 'after' : 'before'](placeholder);
                        				placeholders.not(placeholder).detach();
                        			}
                        			return false;
                        		});
                        	});
                        };
                        })(jQuery);
                    }                                        
                    $('#myTags').sortable().bind('sortupdate',function() {});
                
                    //记录产品id
                    var ids = $('.plus-tag a');
                    var arr = new Array();
                    $('.plus-tag a').each(function(i){
                        arr[i] = $(this).attr('value');
                    });
                    $('#product').val(arr);
                }",

                "select2:close" => "function(){
                    setTimeout('showTime()',100);
               }",

            ]
        ]);
        ?>
    </div>
    <div class="help-block product-error" style="color:#dd4b39;margin-left:32%"></div>
    
    <!--标签列表-->
    <div class="demo" style="clear:both;">
        <div class="plus-tag tagbtn clearfix" id="myTags">
            <?php if(isset($productList)){
                $data = "";
                foreach($productList as $key=> $val){
                    $data .= "<a value=". $val['id'] ." title=". $val['product_name'] ." href='javascript:void(0);'><span>". $val['product_name'] ."</span><em></em></a>";
                }
                echo $data;
            }?>
        </div>
    </div>
    <div style="display:none;"><?= $form->field($model, 'product')->textInput(['id'=>'product']) ?></div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认添加' : '保存', ['class' => 'btn btn-success success','data-uplen' => $model->isNewRecord ? '0' : $uplen]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?=Html::jsFile('@web/js/jquery-1.8.3.min.js')?>
<?=Html::jsFile('@web/js/tag.js')?>
<?=Html::jsFile('@web/js/sortable.js')?>
<script>
//添加产品            
//添加后样式
function showTime(){
//     var data = '<span class="select2-selection__placeholder">请输入产品名称...</span>';
//     $(".plus-tag-add").find(".select2-selection__rendered").html(data);
//     $(".plus-tag-add").find("select option").eq(1).remove();	
}

//删除标签
var a=$(".plus-tag");
$("a em").live("click",function(){
    var c=$(this).parents("a"),b=c.attr("title"),d=c.attr("value");
    delTips(b,d);
    //记录产品id
    var ids = $('.plus-tag a');
    var arr = new Array();
    $('.plus-tag a').each(function(i){
        arr[i] = $(this).attr('value');
    });
    $('#product').val(arr);
    //判断产品个数
    var product_len = $('.plus-tag a').length;
    if(product_len < 3){
    	$('.product-error').html('榜单产品不能少于3个');
        return false;
    }else if(product_len > 10){
        $('.product-error').html('榜单产品不能超过10个');
        return false;
    }else{
    	$('.product-error').html('');
    }
});
//排序
$('#myTags').sortable().bind('sortupdate',function() {});
//编辑页初始产品值
$(function(){
    var ids = $('.plus-tag a');
    var arr = new Array();
    $('.plus-tag a').each(function(i){
        arr[i] = $(this).attr('value');
    });
    $('#product').val(arr);
})
</script>
<?php 
$script = <<<JS
//提交判断产品是否少于3
$('form').on('beforeSubmit', function (e) {
    var product_len = $('.plus-tag a').length;
    if(product_len < 3){
    	$('.product-error').html('榜单产品不能少于3个');
        return false;
    }else if(product_len == 0){
        $('.product-error').html('榜单产品不能为空');
        return false;
    }else{
        $('.product-error').html('');
        var ids = $('.plus-tag a');
        var arr = new Array();
        $('.plus-tag a').each(function(i){
            arr[i] = $(this).attr('value');
        });
        $('#product').val(arr);
        }
});
JS;
$this->registerJs($script);
?>