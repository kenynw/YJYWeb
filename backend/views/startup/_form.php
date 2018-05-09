<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Startup */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="startup-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form']),
    ]); ?>

    <?= $form->field($model, 'title')->textInput() ?>
    
    <?= $form->field($model, 'type')->dropDownList(['0'=>'H5页面','1'=>'产品详情','2'=>'文章详情','3'=>'视频详情','4'=>'话题详情','5'=>'无跳转'],['style'=>'width:100%;']) ?>

    <?= $form->field($model, 'url')->textInput(['style'=>'width:100%','placeholder'=>'请输入H5跳转链接'])->label("链接地址") ?>
    
    <?php //echo $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['style'=>'width:100%;']) ?>

    <div style="float:left;margin-right:10%;width:45%;">
        <?=$form->field($model, 'start_time')->textInput(['id' => 'start_time', 'style'=>'width:100%;','onClick'=>"WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})",'value'=>$model->isNewRecord ? '' : date('Y-m-d H:i:s',$model->start_time)]) ?>
    </div>
    <div style="float:left;width:45%;">
        <?= $form->field($model, 'end_time')->textInput(['id' => 'end_time',  'style'=>'width:100%;','onClick'=>"WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})",'value'=>$model->isNewRecord ? '' : date('Y-m-d H:i:s',$model->end_time)]) ?>
    </div>
   
    <div style="clear:both;">
        <?= $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
            'config'=>[
                'domain_url' => Yii::$app->params['uploadsUrl'],
                'explain' => '推荐尺寸：',
            ],
        ]) ?>
    </div>

    <div class="form-group" style="clear:both;">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?=Html::jsFile('@web/js/My97DatePicker/WdatePicker.js');?>

<?php
$script = <<<JS

$(document).on("change",'#startup-type',function(){
    var type = $(this).val();
    
    if(type == 0){
        $("#startup-url").prev().text("链接地址");
        $("#startup-url").attr("placeholder","请输入H5跳转链接");
        $("#startup-url").attr("style","display:block");
        $(".field-startup-url .control-label").attr("style","display:block");
        $(".field-startup-url .help-block").attr("style","display:block");        
    }else if(type == 1){
        $("#startup-url").prev().text("产品ID");
        $("#startup-url").attr("placeholder","请输入关联的产品ID");
        $("#startup-url").attr("style","display:block");
        $(".field-startup-url .control-label").attr("style","display:block");
        $(".field-startup-url .help-block").attr("style","display:block");        
    }else if(type == 2){
        $("#startup-url").prev().text("文章ID");
        $("#startup-url").attr("placeholder","请输入关联的文章ID");
        $("#startup-url").attr("style","display:block");
        $(".field-startup-url .control-label").attr("style","display:block");
        $(".field-startup-url .help-block").attr("style","display:block");
    }else if(type == 3){
        $("#startup-url").prev().text("视频ID");
        $("#startup-url").attr("placeholder","请输入关联的视频ID");
        $("#startup-url").attr("style","display:block");
        $(".field-startup-url .control-label").attr("style","display:block");
        $(".field-startup-url .help-block").attr("style","display:block");
    }else if(type == 4){
        $("#startup-url").prev().text("话题ID");
        $("#startup-url").attr("placeholder","请输入关联的话题ID");
        $("#startup-url").attr("style","display:block");
        $(".field-startup-url .control-label").attr("style","display:block");
        $(".field-startup-url .help-block").attr("style","display:block");
    }else if(type == 5){
        $("#startup-url").attr("style","display:none");
        $(".field-startup-url .control-label").attr("style","display:none");
        $(".field-startup-url .help-block").attr("style","display:none");
    }
    $("#startup-url").val("");
});

$(document).ready(function(){
    var type = $('#startup-type').val();
    var id = '#startup-url';

    if(type == 0) {
        $("#startup-url").prev().text("链接地址");
        $("#startup-url").attr("placeholder","请输入H5跳转链接");
        $("#startup-url").attr("style","display:block");
    } else if(type == 1) {
        $("#startup-url").prev().text("产品ID");
        $("#startup-url").attr("placeholder","请输入关联的产品ID");
        $("#startup-url").attr("style","display:block");
    } else if (type == 2) {
        $("#startup-url").prev().text("文章ID");
        $("#startup-url").attr("placeholder","请输入关联的文章ID");
        $("#startup-url").attr("style","display:block");
    } else if (type == 3) {
        $("#startup-url").prev().text("视频ID");
        $("#startup-url").attr("placeholder","请输入关联的视频ID");
        $("#startup-url").attr("style","display:block");
    } else if (type == 4) {
        $("#startup-url").prev().text("话题ID");
        $("#startup-url").attr("placeholder","请输入关联的话题ID");
        $("#startup-url").attr("style","display:block");
    } else if (type == 5) {
        $("#startup-url").attr("style","display:none");
    }
})   

JS;
$this->registerJs($script); ?>