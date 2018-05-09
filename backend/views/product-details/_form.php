<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Brand;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
use common\models\ProductCategory;
use backend\models\CommonFun;
use common\models\CommonTag;
use common\models\ProductComponent;
use common\functions\Functions;

/* @var $this yii\web\View */
/* @var $model common\models\ProductDetails */
/* @var $form yii\widgets\ActiveForm */
?>
<br>
<style>
.per_upload_con {width: 200px;}
.table td {border-right:1px solid #ECF0F5}
.table tr:nth-of-type(1) th {border-right:1px solid #ECF0F5}
</style>
<div class="product-details-form">

          <?php $form = ActiveForm::begin(); ?> 
  
            <table class="table" style="border:1px solid #ECF0F5">
            <tr style="font-size: 20px;">
                <th colspan="2">基本信息</th>
                <th colspan="2">详细信息</th>
                <th colspan="2">备案信息</th>
            </tr>
            <tr>
                <td rowspan="5" colspan="2" width="21%"><?php 
               echo $form->field($model, 'product_img')->widget('common\widgets\file_upload\FileUpload',[
                                                'config'=>[
                                                    'domain_url' => Yii::$app->params['uploadsUrl'],
                                                    'explain' => '<b>推荐尺寸：</b>152x152',
                                                ],
                                            ]) ?>
                <th width="80px" style="vertical-align: middle;">品牌</th><td width="20%"><?php $brandData = $model->isNewRecord ? '' : Brand::getBrandList($model->brand_id); 
                echo $form->field($model, 'brand_id')->widget(Select2::classname(), [
                                                              'options' => ['placeholder' => '请输入品牌名称 ...'],
                                                              'data' => $brandData,
                                                              'pluginOptions' => [
                                                                   'placeholder' => 'Waiting...',
                                                                   'language'=>"zh-CN",
                                                                   'minimumInputLength'=> 1,
                                                                   'allowClear' => true,
                                                                   'ajax' => [
                                                                       'url' => 'search-brand',
                                                                       'dataType' => 'json',
                                                                       'cache' => true,
                                                                       'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                                                                   ],
                                                                   'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                                                   'templateResult' => new JsExpression('function(res) { return res.text; }'),
                                                                   'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                                                               ],
                                                          ])->label('');?></td>
                <th width="80px" style="vertical-align: middle;">备案号</th><td style="vertical-align: middle;"><?= $form->field($model, 'standard_number')->textInput(['class' => ''])->label("") ?></td>
            </tr>
            <tr>
                <th>参考价</th><td><?= $form->field($model, 'price')->textInput(['class' => ''])->label("") ?></td>
                <th>批准日期</th><td><?php 
                $value = $model->product_date == '1970-01-01' ? '' : $model->product_date;
                echo '<div class="input-group drp-container">'.
                DateRangePicker::widget([
                    'options' => ['class' => ''],
                    'value' => $value,
                    'name'=>'ProductDetails[product_date]',
                    'id'=>'productdetails-product_date',
                    'useWithAddon'=>true,
                    'pluginOptions'=>[
                        'singleDatePicker'=>true,
                        'showDropdowns'=>true
                    ]
                ]).'</div>';
                ?></td>
            </tr>
            <tr>           
                <th>规格</th>
                <td><input type="text" id="productdetails-form" name="ProductDetails[form]" value=<?=preg_replace('{\D+$}','', $model->form); ?>>
                        <select id="productdetails-unit" name="ProductDetails[unit]">
                        <?php $sel = preg_replace('{^[0-9]+(.[0-9]{1,2})?}','', $model->form);?>
                        <option value="0" >ml</option>
                        <option value="1" <?= $sel == 'g' ? 'selected' : ''?> >g</option>
                        <option value="2" <?= $sel == '片' ? 'selected' : ''?> >片</option>
                </td>
                <th>生产国</th><td><?= $form->field($model, 'product_country')->textInput(['class' => ''])->label("") ?></td>
            </tr>
            <tr>           
                <th>星级</th>
                <td>
                    <?= $form->field($model, 'star')->dropDownList(['1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星'],['class'=>''])->label('') ?>
                </td>
                <th>生产企业（中）</th><td><?= $form->field($model, 'product_company')->textInput(['class' => ''])->label("") ?></td>
            </tr>
            <tr>           
                <th>状态</th>
                <td>
                    <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['class'=>''])->label('') ?>
                </td>
                <th>生产企业（英）</th><td><?= $form->field($model, 'en_product_company')->textInput(['class' => ''])->label("") ?></td>
            </tr>
            <tr>   
                <th width="4%">产品ID</th><td><?=$model->id ?></td>                        
                <th>所属分类</th>
                <td>
<!--                     <select id="productdetails-cate_id" name="ProductDetails[cate_id]"> -->
                     <?= $form->field($model, 'cate_id')->dropDownList( $cateList,['style'=>'width:120px;','class' => '','prompt'=>'--- 请选择 ---'])->label("") ?>
                </td>
                <th></th><td></td>
            </tr>
            <tr>     
                <th>产品名</th><td><?= $form->field($model, 'product_name')->textInput(['class' => ''])->label("") ?></td>       
                <th>是否推荐</th>
                <td>
                    <?= $form->field($model, 'is_recommend')->dropDownList(['0'=>'默认','1'=>'推荐'],['class'=>''])->label('') ?>
                </td>
                <th></th><td></td>
            </tr>
            <tr>     
                <th>别名</th><td><?= $form->field($model, 'remark')->textInput(['class' => ''])->label("") ?></td>      
                <th>是否上榜</th><td><?= $form->field($model, 'is_top')->dropDownList(['0'=>'默认','1'=>'上榜'],['class' => ''])->label("") ?></td>
                <th></th><td></td>
            </tr>
        </table>
        
        <label>官方购买渠道</label>
        <div style="float:left;border:1px #ddd solid;width:100%;padding:10px;">
        <div style="float:left;margin-right:60px;">
        <?= $form->field($model, 'link1')->textInput(['placeholder' => '请输入正确url','style'=>'width:600px;'])->label('淘宝') ?>  
        </div>  
        <div style="float:left;margin-right:60px;">
        <?= $form->field($model, 'tb_goods_id1')->textInput(['style'=>'width:150px;'])->label('关联平台的商品id') ?>    
        </div>  
        <div style="clear:both;float:left;margin-right:60px;">
        <?= $form->field($model, 'link2')->textInput(['placeholder' => '请输入正确url','style'=>'width:600px;'])->label('京东') ?>    
        </div>   
        <div style="float:left;margin-right:60px;">
        <?= $form->field($model, 'tb_goods_id2')->textInput(['style'=>'width:150px;'])->label('关联平台的商品id') ?>    
        </div> 
        <div style="clear:both;float:left;margin-right:60px;">
        <?= $form->field($model, 'link3')->textInput(['placeholder' => '请输入正确url','style'=>'width:600px;'])->label('亚马逊') ?>    
        </div>   
        <div style="float:left;margin-right:60px;">
        <?= $form->field($model, 'tb_goods_id3')->textInput(['style'=>'width:150px;'])->label('关联平台的商品id') ?>    
        </div> 
        </div>
        <br>
        
        <?= $form->field($model, 'product_explain')->textarea() ?>        
        
        <!-- 新标签1 -->
        <input type="hidden" class="new_tag" name="ProductDetails[new_tag]" value="">
        <?php   
        $tagData =  $model->isNewRecord ? '' : CommonFun::getKeyValArr(new CommonTag(), 'tagid', 'tagname',Functions::db_create_in($tagIdArr,'tagid'));
        $model->tag_name =  $model->isNewRecord ? '' : $tagIdArr;
        echo $form->field($model, 'tag_name', ['labelOptions' => ['label' => '产品功效<span style="color:red">(最多3个标签)</span>','class' => 'control-label']])->widget(Select2::classname(), [
                       'options' => ['placeholder' => '请输入产品功效名称 ...','multiple' => true],
                       'data' => $tagData,
                       'showToggleAll' => false,
                       'maintainOrder' => true,
                       'pluginOptions' => [
                           'placeholder' => 'Waiting...',
                           'language'=>"zh-CN",
                           'minimumInputLength'=> 1,
                           'maximumInputLength'=> 20,
                           'tags' => true,
                           'ajax' => [
                               'url' => 'search-tag',
                               'dataType' => 'json',
                               'cache' => true,
                               'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                           ],
                           'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                           'templateResult' => new JsExpression('function(res) { return res.text; }'),
                           'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                       ],
                ]);
        ?>
        
        <!-- 新标签2 -->
        <input type="hidden" class="new_tag2" name="ProductDetails[new_tag2]" value="">
        <?php   
        $tagData2 =  $model->isNewRecord ? '' : CommonFun::getKeyValArr(new CommonTag(), 'tagid', 'tagname',Functions::db_create_in($tagIdArr2,'tagid'));
        $model->tag_name2 =  $model->isNewRecord ? '' : $tagIdArr2;
        echo $form->field($model, 'tag_name2', ['labelOptions' => ['label' => '产品标签<span style="color:red">(最多2个标签)</span>','class' => 'control-label']])->widget(Select2::classname(), [
                       'options' => ['placeholder' => '请输入产品标签名称 ...','multiple' => true],
                       'data' => $tagData2,
                       'showToggleAll' => false,
                       'maintainOrder' => true,
                       'pluginOptions' => [
                           'placeholder' => 'Waiting...',
                           'language'=>"zh-CN",
                           'minimumInputLength'=> 1,
                           'maximumInputLength'=> 20,
                           'tags' => true,
                           'ajax' => [
                               'url' => 'search-tag?type=3',
                               'dataType' => 'json',
                               'cache' => true,
                               'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                           ],
                           'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                           'templateResult' => new JsExpression('function(res) { return res.text; }'),
                           'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                       ],
                ]);
        ?>
        
        <!-- 成分 -->                                   
        <?php 
        $componentData = $model->isNewRecord ? '' : CommonFun::getKeyValArr(new ProductComponent(), 'id', 'name',Functions::db_create_in($cateIdArr,'id'));
        $model->component_id =  $model->isNewRecord ? '' : $cateIdArr;
        
        echo $form->field($model, 'component_id',['labelOptions' => ['label' => '成分&emsp;<button type="button" class="btn btn-default btn-xs take_component">抓取成分</button>','class' => 'control-label']])->widget(Select2::classname(), [
                       'options' => ['placeholder' => '请输入成分名称 ...','multiple' => true],
                       'data' => $componentData,
                       'showToggleAll' => false,
                       'maintainOrder' => true,
                       'pluginOptions' => [
                           'placeholder' => 'Waiting...',
                           'language'=>"zh-CN",
                           'minimumInputLength'=> 1,
                           'ajax' => [
                               'url' => 'search-component',
                               'dataType' => 'json',
                               'cache' => true,
                               'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                           ],
                           'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                           'templateResult' => new JsExpression('function(res) { return res.text; }'),
                           'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                       ],
                ]);
        ?>
    <span class="component_error" style="color:red;"></span> 

    <div class="form-group" style="margin-left: 2px;margin-top:80px">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php 
$script = <<<JS
//无品牌不上榜
$(document).on("change",'#productdetails-brand_id',function(){
    $(this).val() == '' ? $('#productdetails-is_top>option:nth-child(2)').attr("disabled","disabled") : $('#productdetails-is_top>option:nth-child(2)').removeAttr("disabled");
    $(this).val() == '' ? $('#productdetails-is_top>option:nth-child(2)').html("上榜(请选择相应品牌)") : $('#productdetails-is_top>option:nth-child(2)').html("上榜");
})
$(function(){
    $('#productdetails-brand_id').val() == '' ? $('#productdetails-is_top>option:nth-child(2)').attr("disabled","disabled") : $('#productdetails-is_top>option:nth-child(2)').removeAttr("disabled");  
    $('#productdetails-brand_id').val() == '' ? $('#productdetails-is_top>option:nth-child(2)').html("上榜(请选择相应品牌)") : $('#productdetails-is_top>option:nth-child(2)').html("上榜");
});

//关键词
$('form').on('beforeSubmit', function (e) {
    //标签1
    var new_tag_option = [];
    var old_tag_option = [];
    var new_tag = '';
    
    $('#productdetails-tag_name option').each(function(index) {
        if($(this).attr('data-select2-tag') == 'true'){
            new_tag_option[index] = $(this).val();
        }else{
            old_tag_option[index] = $(this).html();
        }
    });
    
    $('.field-productdetails-tag_name .select2-container .select2-selection__rendered li').each(function() {
        if(($.inArray($(this).attr('title'), new_tag_option) != -1) && ($.inArray($(this).attr('title'), old_tag_option) == -1)){
            new_tag += $(this).attr('title')+',';
        }           
    });
    
    $('#productdetails-tag_name option').each(function() {
        if($(this).attr('data-select2-tag') == 'true'){
            $(this).remove();
        }
    });
// console.log(new_tag_option);console.log(old_tag_option);console.log(new_tag);
    $('.new_tag').val(new_tag);
    
    //标签2
    var new_tag_option2 = [];
    var old_tag_option2 = [];
    var new_tag2 = '';
    
    $('#productdetails-tag_name2 option').each(function(index) {
        if($(this).attr('data-select2-tag') == 'true'){
            new_tag_option2[index] = $(this).val();
        }else{
            old_tag_option2[index] = $(this).html();
        }
    });
    
    $('.field-productdetails-tag_name2 .select2-container .select2-selection__rendered li').each(function() {
        if(($.inArray($(this).attr('title'), new_tag_option2) != -1) && ($.inArray($(this).attr('title'), old_tag_option2) == -1)){
            new_tag2 += $(this).attr('title')+',';
        }           
    });
    
    $('#productdetails-tag_name2 option').each(function() {
        if($(this).attr('data-select2-tag') == 'true'){
            $(this).remove();
        }
    });
// console.log(new_tag_option);console.log(old_tag_option);console.log(new_tag);
    $('.new_tag2').val(new_tag2);
});

//抓取成分
$('body').on('click', '.take_component',function () {
    var name = $("#productdetails-product_name").val();
    var self = $(this);
    self.html('抓取中...');
    self.attr('disabled','disabled');
    $('.component_error').html("");
    var arr = [];
    
     $.ajax({
        url: '/product-details/take-component',
        type: 'post',
        dataType: 'json',
        data:{name:name},
        success : function(data) {
            if (data.status == "0") {
                alert(data.msg);
                self.html('抓取成分');
            }
            if (data.status == "1") {
                var result = eval(data.msg);
                var length = result.length;
    
                $('.field-productdetails-component_id #productdetails-component_id').empty();
		        for(var i = 0;i<length;i++) {
                    $('.field-productdetails-component_id #productdetails-component_id').prepend('<option value="'+result[i]['id']+'">'+result[i]['name']+'</option>');
    
                    arr[i] = result[i]['id'];
                }
                $("#productdetails-component_id").val(arr).trigger('change'); 
    
                self.html('抓取成功');
                self.attr('disabled',false);
    
                if(data.error != ''){
                    $('.component_error').html("抓取失败的成分有："+data.error);
                }
            } else {
                self.html('抓取成分');
                self.attr('disabled',false);
            }
        },
        error : function(data) {
            alert("抓取失败");
            self.html('抓取成分');
            self.attr('disabled',false);
        }
    });
});
JS;
$this->registerJs($script);
?>