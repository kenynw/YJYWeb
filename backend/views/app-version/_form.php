<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AppVersion */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="app-version-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="form-group field-article-content required">
        <label class=" control-label" for="article-content">更新内容</label>
        <?php echo kucha\ueditor\UEditor::widget([ 'model' => $model, 'attribute' => 'content' ,
            'clientOptions' => [
                'initialFrameHeight' => '200',
                'initialFrameWidth' => '60%',
                'lang' =>'zh-cn',
                'allowDivTransToP' => false,
                'wordCount' => false,
                'elementPathEnabled' => false,
                'toolbars' => [
                    [
                        //'source', //源代码
                        'undo', //撤销
                        'redo', //重做
                        'bold', //加粗
                        'indent', //首行缩进
                        'italic', //斜体
                        'underline', //下划线
                        'strikethrough', //删除线
                        'formatmatch', //格式刷
                        'selectall', //全选
                        'removeformat', //清除格式
                        'cleardoc', //清空文档
                        'fontfamily', //字体
                        'fontsize', //字号
                        'paragraph', //段落格式
                        'justifyleft', //居左对齐
                        'justifyright', //居右对齐
                        'justifycenter', //居中对齐
                        'justifyjustify', //两端对齐
                        'forecolor', //字体颜色
                        'backcolor', //背景色
                        'insertorderedlist', //有序列表
                        'insertunorderedlist', //无序列表
                        'rowspacingtop', //段前距
                        'rowspacingbottom', //段后距
                        'lineheight', //行间距
                        'edittip ', //编辑提示
                        'autotypeset', //自动排版
                        'background', //背景
                    ]
                ]
            ] ])
        ?>
        <div class="help-block"></div>
    </div>

    <?php //echo $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <br>
    <?= $form->field($model, 'number')->textInput(['style'=>'width:500px;']) ?>

    <div style="float:left;margin-right:70px;">
        <?= $form->field($model, 'type')->dropDownList(['1'=>'android','2'=>'ios'],['style'=>'width:120px;']) ?>
    </div>
    <div style="float:left;margin-right:70px;">
        <?= $form->field($model, 'status')->dropDownList(['1'=>'上线','0'=>'下线'],['style'=>'width:120px;']) ?>
    </div>
    <div style="float:left;">
        <?= $form->field($model, 'isMust')->dropDownList(['1'=>'是','0'=>'否'],['style'=>'width:120px;']) ?>
    </div>
    <div style="clear:both"></div>

    <?= $form->field($model, 'downloadUrl',['labelOptions' => ['label' => '下载地址<span style="color:red" class="aspan">（android类型 请上传文件）</span>','class' => 'control-label']])->textInput(['style'=>'width:500px;']) ?>
    <input type="file" id="upload1"  name="AppVersion[downloadUrl]"/><br/>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success new' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS

$(document).on("change",'#upload1',function(){
    $("#appversion-downloadurl").val($(this).val());
});
  
$(function(){
    var type = $("#appversion-type").val();
    if(type == 2){
        $("#upload1").hide();
        $("#appversion-downloadurl").attr("readOnly",false);
    }else{
        $("#upload1").show();
        $("#appversion-downloadurl").attr("readOnly",true);
    } 
});

// $(document).on("focus",'#appversion-downloadurl',function(){
//     var type = $("#appversion-type").val();
//     if(type == 1){
//         alert("android类型 请上传文件");
//     }
// });
$(document).on("change",'#appversion-type',function(){
    var type = $("#appversion-type").val();
    if(type == 1){
        $('.field-appversion-downloadurl .control-label').append('<span style="color:red" class="aspan">（android类型 请上传文件）</span>');
    } else {
        $('.aspan').remove();
    }
})

$(document).on("change",'#appversion-type',function(){
    $("#appversion-downloadurl").val("");
    if($(this).val() == 2){
        $("#upload1").hide();
        $("#appversion-downloadurl").attr("readOnly",false);
    }else{
        $("#upload1").show();
        $("#appversion-downloadurl").attr("readOnly","true");
    }
});

//百度编辑器初始化
var ue = UE.getEditor('appversion-content');




// $('.new').on('click', function (e) {
    
//     //判断内容是否为空
//     var check = ue.hasContents();
//     if(check == false){
//         $(".field-article-content").addClass("has-error");
//         $(".field-article-content").find(".help-block").html("  文章内容不能为空。");
//         return false;
//     }
    
// });


JS;
$this->registerJs($script);
?>
