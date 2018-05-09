<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\assets\AppAsset;
AppAsset::register($this);
use yii\helpers\Url;

AppAsset::addCss($this,Yii::$app->request->baseUrl."/css/tab.css");
?>



<div class="product-component-form" style="padding:30px;">
    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form','id'=>$model->isNewRecord ? '' : $model->id]),
    ]); ?>

    <div>
        <div style="float:left;width:55%;margin-right:5%">
            <?= $form->field($model, 'name')->textInput([]) ?>
        </div>
        <div style="float:left;width:35%">
            <?= $form->field($model, 'ename')->textInput() ?>
        </div>
    </div>

    <div>
        <div style="float:left;width:55%;margin-right:5%">
            <?= $form->field($model, 'alias')->textInput() ?>
        </div>
    </div>

    <div>
        <div style="float:left;margin-right:50px;">
            <?= $form->field($model, 'risk_grade')->textInput(['style'=>'width:150px']) ?>
        </div>
        <div style="float:left;margin-right:50px;">
            <?= $form->field($model, 'is_active')->dropDownList(['1'=>'有','0'=>'无'],['style'=>'width:120px']) ?>
        </div>
        <div style="float:left;">
            <?= $form->field($model, 'is_pox')->dropDownList(['1'=>'是','0'=>'否'],['style'=>'width:120px']) ?>
        </div>
    </div>

    <div  style="clear:both;"></div>

    <?php //echo $form->field($model, 'component_action')->textInput(['placeholder' => '多个用中文，拼接']) ?>
    <div style="display:none;"><?= $form->field($model, 'component_action')->textInput(['id'=>'tags']) ?></div>


    <label>使用目的：</label>
    <div class="plus-tag tagbtn clearfix" id="myTags">
        <?php if(!empty($model->component_action)){
            $data = "";
            $tagList = explode("，",$model->component_action);
            foreach($tagList as $val){
                $data .= "<a value=". $val ." title=". $val ." href='javascript:void(0);'><span>". $val ."</span><em></em></a>";
            }
            echo $data;
        }?>
    </div>

    <div class="plus-tag-add">
        <div class="Form FancyForm">
            <div style="float:left;margin-right:20px;">
                <input id="" name="" type="text"/>
            </div>
            <button type="button" class="btn btn-info btn-sm" style="float:left;">添加</button>
            <a href="javascript:void(0);">展开常用列表</a>
        </div>
    </div><!--plus-tag-add end-->

    <div id="mycard-plus" style="display:none;clear:both;">
        <div class="default-tag tagbtn">
            <a value="-1" title="保湿剂" href="javascript:void(0);"><span>保湿剂</span><em></em></a>
            <a value="-1" title="抗氧化剂" href="javascript:void(0);"><span>抗氧化剂</span><em></em></a>
            <a value="-1" title="清洁剂" href="javascript:void(0);"><span>清洁剂</span><em></em></a>
            <a value="-1" title="物理防晒剂" href="javascript:void(0);"><span>物理防晒剂</span><em></em></a>
            <a value="-1" title="化学防晒剂" href="javascript:void(0);"><span>化学防晒剂</span><em></em></a>
            <a value="-1" title="美白剂" href="javascript:void(0);"><span>美白剂</span><em></em></a>
            <a value="-1" title="防腐剂" href="javascript:void(0);"><span>防腐剂</span><em></em></a>
            <a value="-1" title="表面活性剂" href="javascript:void(0);"><span>表面活性剂</span><em></em></a>
            <a value="-1" title="增泡剂" href="javascript:void(0);"><span>增泡剂</span><em></em></a>
            <a value="-1" title="抗菌剂" href="javascript:void(0);"><span>抗菌剂</span><em></em></a>
            <a value="-1" title="乳化剂" href="javascript:void(0);"><span>乳化剂</span><em></em></a>
            <a value="-1" title="柔润剂" href="javascript:void(0);"><span>柔润剂</span><em></em></a>
            <a value="-1" title="pH调节剂" href="javascript:void(0);"><span>pH调节剂</span><em></em></a>
            <a value="-1" title="舒缓抗敏" href="javascript:void(0);"><span>舒缓抗敏</span><em></em></a>
            <a value="-1" title="抗静电" href="javascript:void(0);"><span>抗静电</span><em></em></a>
            <a value="-1" title="气味抑制剂" href="javascript:void(0);"><span>气味抑制剂</span><em></em></a>
        </div>
    </div><!--mycard-plus end-->

    <div style="clear:both;">
        <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
    </div>

    <div class="form-group" style="clear:both;">
        <?= Html::submitButton($model->isNewRecord ? '确认添加' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
AppAsset::addScript($this, Yii::$app->request->baseUrl . '/js/tab.js');

$script = <<<JS

$(document).on("blur",'#productcomponent-name',function(){
    
    var name = $(this).val();
    var url = '/product-component/check-name';
    var _this = $(this);
    var id = "$model->id";
    
    $.get(url, { id : id , name: name },
        function (data) {
            if(data == 1){
                window.setTimeout(function(){
                    _this.parent("div").addClass("has-error");
                    _this.parent("div").find(".help-block").html("  成分名已经存在。");
                },100); 
            }
        }  
    );
    
});

$('form').on('beforeSubmit', function (e) {
 
    //使用目的处理
    var str = "";
    $(".plus-tag a").each(function(i){
        str += $(this).attr("title") + "，";
    });
    str = str.substring(0, str.lastIndexOf('，')); 
    $('#tags').val(str);

    var name = $("#productcomponent-name").val();
    var url = '/product-component/check-name';
    var check = 1;
    var id = "$model->id";
    
     $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        async: false,
        data:{ id : id , name: name },
        success : function(data) {
            if(data == 1){
                window.setTimeout(function(){
                    $("#productcomponent-name").parent("div").addClass("has-error");
                    $("#productcomponent-name").parent("div").find(".help-block").html("  成分名已经存在。");
                },2);
                check = 0;
            }
        },
    });
    
    if(check == 0){
        return false;
    }
    
    $(':submit').attr('disabled', true).addClass('disabled');
});

JS;
$this->registerJs($script);
?>

