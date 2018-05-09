<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Video */
/* @var $form yii\widgets\ActiveForm */
?>
<?=Html::cssFile('@web/css/loading/ladda-themeless.min.css')?>
<div class="video-form2">

    <?php $form = ActiveForm::begin(['id' => 'form2']); ?>
    
    <div class="form-group field-video-url">
    <label class="control-label" for="video-title">按Url添加</label>
    <input type="text" id="video-url" class="form-control" name="Video[url]">    
    <div class="help-block"></div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('提交', ['class' => 'btn btn-success sub ladda-button','data-style' => 'expand-left']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?=Html::jsFile('@web/js/loading/spin.min.js')?>
<?=Html::jsFile('@web/js/loading/ladda.min.js')?>
<?php 
$script = <<<JS
$(function(){
    //验证
    $('.sub').on('click', function () { 
        var l = Ladda.create(this);
    
        //不能为空
        if($("#video-url").val() != "") {
            $("#video-url").parent().removeClass('has-error');
            $("#video-url").next().html('');
    	 	l.start();
            document.getElementById("form2").submit();
        } else {
            $("#video-url").parent().addClass('has-error');
            $("#video-url").next().html('地址不能为空');
            return false;
        }
        //url格式
        
        if(checkUrl($("#video-url").val())) {
            $("#video-url").parent().removeClass('has-error');
            $("#video-url").next().html('');
    	 	l.start();
            document.getElementById("form2").submit();
        } else {
            $("#video-url").parent().addClass('has-error');
            $("#video-url").next().html('url格式不对');
            return false;
        }
    });
})
JS;
$this->registerJs($script);
?>