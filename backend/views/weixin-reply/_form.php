<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\WeixinReply */
/* @var $form yii\widgets\ActiveForm */
?>
<meta name="referrer" content="never">
<meta http-equiv="Expires" content="0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-control" content="no-cache">
<meta http-equiv="Cache" content="no-cache">
<div class="weixin-reply-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form']),
    ]); ?>

    <?= $form->field($model, 'keyword')->textInput([]) ?>
    
    <?= $form->field($model, 'type')->dropDownList(['1'=>'文本回复','3'=>'图片','4'=>'文章','0'=>'默认回复']) ?>
    
    <?= $form->field($model, 'match_mode')->dropDownList(['equal'=>'完全匹配','contain'=>'部分匹配']) ?>
    
    <!-- 文本回复 -->
    <div id="reply_text_div"> 
        <?php //echo $form->field($model, 'reply')->widget(\yii\redactor\widgets\Redactor::className(),[
//             'clientOptions' => [ 
    //             'imageManagerJson' => ['/redactor/upload/image-json'], 
    //             'imageUpload' => ['/redactor/upload/image'], 
    //             'fileUpload' => ['/redactor/upload/file'], 
//                 'lang' => 'zh_cn', 
    //             'plugins' => ['clips', 'fontcolor','imagemanager'] 
//             ],
//             'options' => [
//                 'row' => '5'
//             ]
//         ]) ?>
        <?= $form->field($model, 'reply')->textarea(['rows' => 5]) ?>
    </div>

    <!-- 素材回复 -->
    <div id="material_div" style="margin-bottom: 80px">

    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS
//分页
$(document).on('click', '.wx_pagination .click', function () {
    var page = $('.wx_pagination .page span').attr("data-page");
    var type = $('.wx_pagination').attr("data-type");

    if($(this).hasClass('next')){
        page = parseInt(page) + 1;
    }else if($(this).hasClass('prev')){
        page = parseInt(page) - 1;
    }

    $.ajax({
        url: 'material-list',
        type: 'post',
        dataType: 'json',
        data:{page:page,type:type},
        success : function(data) {
            $('#material_div').html(data);
        },
        error : function(data) {
            alert('操作失败！');
        }
    });

});

//回复类型改变时
$(document).on("change",'#weixinreply-type',function(){
    var type = $(this).val();
    
    if(type == 3 || type == 4){
        var page = 1;
        var type = type == 3 ? 'image' : 'news';
        
        $.ajax({
            url: 'material-list',
            type: 'post',
            dataType: 'json',
            data:{page:page,type:type},
            success : function(data) {
                $('#material_div').html(data);
            },
            error : function(data) {
                alert('操作失败！');
            }
        });
    } else {
        $('#material_div').empty();
        $('#weixinreply-reply').val('');
    }
});

//编辑页面初始状
$(document).ready(function(){
    var type = $('#weixinreply-type').val();

    if(type == 3 || type == 4){
        var page = 1;
        var type = type == 3 ? 'image' : 'news';
        
        $.ajax({
            url: 'material-list',
            type: 'post',
            dataType: 'json',
            data:{page:page,type:type},
            success : function(data) {
                $('#material_div').html(data);
            },
            error : function(data) {
                alert('操作失败！');
            }
        });
    } else {
        $('#material_div').empty();
        $('#weixinreply-reply').val('');
    }
})  

//特定页数跳转
$(document).on('click', '.wx_pagination .go a', function () {
    var page = $('.wx_pagination .go input').val();
    if(page){
        var type = $('#weixinreply-type').val();
        var type = type == 3 ? 'image' : 'news';
    
        $.ajax({
            url: 'material-list',
            type: 'post',
            dataType: 'json',
            data:{page:page,type:type},
            success : function(data) {
                $('#material_div').html(data);
            },
            error : function(data) {
                alert('操作失败！');
            }
        });
    }else{
        alert("请输入页数");
    }
});

//图片点击
$(document).on('click', '.wx_list img', function () {
    $('.wx_list img').attr("style","width:200px;height:200px;");
    $(this).attr("style","width:200px;height:200px;border: 2px solid #3c8dbc;border-radius: 4px;");
    
    var media_id = $(this).attr('data-id');
    $('#weixinreply-reply').val(media_id);
})

//文章点击
$(document).on('click', '.click-box', function () {
    $('.click-box').attr("style","");
    $(this).attr("style","border: 2px solid #3c8dbc;border-radius: 4px;");
    
    var media_id = $(this).attr('data-appid');
    $('#weixinreply-reply').val(media_id);
})
JS;
$this->registerJs($script); ?>