<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\file_upload\FileUpload;
use yii\helpers\Url;

?>

<div class="banner-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form']),
    ]); ?>

    <div>
        <div style="float:left;width:50%;">
            <?= $form->field($model, 'title',['labelOptions' => ['label' => 'banner标题<span style="color:red"></span>','class' => 'control-label']])->textInput(['maxlength' => false,'style'=>'width:100%']) ?>

            <div style="float:left;width:30%;">
                <?= $form->field($model, 'type')->dropDownList(['1'=>'H5页面','2'=>'产品详情','3'=>'文章详情','5'=> '话题详情','4'=>'无跳转'],['style'=>'width:100%;']) ?>
            </div>
            <div style="float:left;margin-left:5%;width:30%;">
                <?= $form->field($model, 'position')->dropDownList(['1'=>'APP首页','2'=>'APP发现页','3'=>'H5首页'],['style'=>'width:100%;']) ?>
            </div>
            <div style="float:left;margin-left:5%;width:30%;">
                <?= $form->field($model, 'sort_id')->textInput(['value' => $model->sort_id?$model->sort_id:0,'style'=>'width:100%;']) ?>
            </div>

            <?= $form->field($model, 'url')->textInput(['style'=>'width:100%','placeholder'=>'请输入H5跳转链接'])->label("链接地址") ?>

            <div style="float:left;margin-right:7%;width:45%;">
                <?=$form->field($model, 'start_time')->textInput(['id' => 'start_time', 'style'=>'width:100%;','onClick'=>"WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})",'value'=>$model->isNewRecord ? '' : date('Y-m-d H:i:s',$model->start_time)]) ?>
            </div>
            <div style="float:left;width:45%;">
                <?= $form->field($model, 'end_time')->textInput(['id' => 'end_time',  'style'=>'width:100%;','onClick'=>"WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})",'value'=>$model->isNewRecord ? '' : date('Y-m-d H:i:s',$model->end_time)]) ?>
            </div>

        </div>

        <div style="float:left;padding-left:8%;">
            <?= $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
                'config'=>[
                    'domain_url' => Yii::$app->params['uploadsUrl'],
                    'explain' => '推荐尺寸：640x400',
                ],
            ]) ?>
        </div>

    </div>


    <div class="form-group" style="clear:both;">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?=Html::jsFile('@web/js/My97DatePicker/WdatePicker.js');?>

<?php
$script = <<<JS

$(document).on("change",'#banner-type',function(){
    var type = $(this).val();
    
    if(type == 1){
        $("#banner-url").prev().text("链接地址");
        $("#banner-url").attr("placeholder","请输入H5跳转链接");
        $("#banner-url").attr("style","display:block");
        $(".field-banner-url .control-label").attr("style","display:block");
        $(".field-banner-url .help-block").attr("style","display:block");        
    }else if(type == 2){
        $("#banner-url").prev().text("产品ID");
        $("#banner-url").attr("placeholder","请输入关联的产品ID");
        $("#banner-url").attr("style","display:block");
        $(".field-banner-url .control-label").attr("style","display:block");
        $(".field-banner-url .help-block").attr("style","display:block");        
    }else if(type == 3){
        $("#banner-url").prev().text("文章ID");
        $("#banner-url").attr("placeholder","请输入关联的文章ID");
        $("#banner-url").attr("style","display:block");
        $(".field-banner-url .control-label").attr("style","display:block");
        $(".field-banner-url .help-block").attr("style","display:block");
    }else if(type == 4){
        $("#banner-url").attr("style","display:none");
        $(".field-banner-url .control-label").attr("style","display:none");
        $(".field-banner-url .help-block").attr("style","display:none");
    }else if(type == 5){
        $("#banner-url").prev().text("话题ID");
        $("#banner-url").attr("placeholder","请输入关联的话题ID");
        $("#banner-url").attr("style","display:block");
        $(".field-banner-url .control-label").attr("style","display:block");
        $(".field-banner-url .help-block").attr("style","display:block");
    }
    $("#banner-url").val("");
});

$(document).ready(function(){
    var type = $('#banner-type').val();
    var id = '#banner-url';

    if(type == 1) {
        $("#banner-url").prev().text("链接地址");
        $("#banner-url").attr("placeholder","请输入H5跳转链接");
        $("#banner-url").attr("style","display:block");
    } else if(type == 2) {
        $("#banner-url").prev().text("产品ID");
        $("#banner-url").attr("placeholder","请输入关联的产品ID");
        $("#banner-url").attr("style","display:block");
    } else if (type == 3) {
        $("#banner-url").prev().text("文章ID");
        $("#banner-url").attr("placeholder","请输入关联的文章ID");
        $("#banner-url").attr("style","display:block");
    } else if (type == 4) {
        $("#banner-url").attr("style","display:none");
    } else if (type == 5) {
        $("#banner-url").prev().text("话题ID");
        $("#banner-url").attr("placeholder","请输入关联的话题ID");
        $("#banner-url").attr("style","display:block");
    }
})

//位置对应推荐尺寸                    
$(document).on("change",'#banner-position',function(){
    var type = $(this).val();
    var relation_id = "#huodongspecialconfig-relation";
    
    if(type == 1){
        $('.per_upload_img').html('<b>推荐大小：</b>小于1M<br>推荐尺寸：640x400');
    }else if(type == 2){
        $('.per_upload_img').html('<b>推荐大小：</b>小于1M<br>推荐尺寸：750x250');
    }else{
        $('.per_upload_img').html('<b>推荐大小：</b>小于1M<br>推荐尺寸：640x270');    
    }
});    

JS;
$this->registerJs($script); ?>