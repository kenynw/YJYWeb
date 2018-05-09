<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\file_upload\FileUpload;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <div style="width:100%">
        <div style="float:left;width:50%">
            <?= $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
                'config'=>[
                    'domain_url' => Yii::$app->params['uploadsUrl'],
                    'explain' => '推荐尺寸：69x69',
                ],
            ]) ?>
        </div>

        <div style="float:left;">
            <?= $form->field($model, 'username')->textInput(['style'=>'width:90%']) ?>
            <?=$form->field($model, 'birth_date')->textInput(['id' => 'birth_date','style'=>'width:90%;'])->label('年龄') ?>
            <?= $form->field($model, 'skin_id')->dropDownList( $skinList,['style'=>'width:90%;','prompt'=>'请选择']) ?>
        </div>
        <div class="error" style="color:#dd4b39;clear:both"></div>

    </div>

    <div class="form-group" style="clear:both;margin-left:35%;">
        <?= Html::submitButton($model->isNewRecord ? '确认创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php


$script = <<<JS

// $('#birth_date').datepicker({
//     autoclose: true,
//     format : 'yyyy-mm-dd',
//     'language' : 'zh-CN',
// });

//马甲名重复
$(document).on("blur",'#user-username',function(){
    
    var name = $(this).val();
    var url = '/user/check-name';
    var _this = $(this);
    var id = "$model->id";
    
    $.get(url, { id : id , name: name },
        function (data) {
            if(data == 1){
                window.setTimeout(function(){
                    _this.parent("div").addClass("has-error");
                    _this.parent("div").find(".help-block").html("  马甲名已经存在。");
                },300); 
            }
        }  
    );
    
});

$('form').on('beforeSubmit', function (e) {
 
    var name = $("#user-username").val();
    var url = '/user/check-name';
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
                    $("#user-username").parent("div").addClass("has-error");
                    $("#user-username").parent("div").find(".help-block").html("  马甲名已经存在。");
                },2);
                check = 0;
            }
        },
    });
    
    if(check == 0){
        return false;
    }
    
    //验证其他信息必填
    var img = $("#user-img").val().trim();
    var date = $("#birth_date").val().trim();
    var skin = $("#user-skin_id").val().trim();
    if(img == '' || date == '' || skin == ''){
        $('.error').html("信息项必须全填。");
        return false;
    }else{
        $('.error').html("");
    }
    
    $(':submit').attr('disabled', true).addClass('disabled');
});

JS;
$this->registerJs($script);
?>
