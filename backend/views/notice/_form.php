<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url

/* @var $this yii\web\View */
/* @var $model common\models\Notice */
/* @var $form yii\widgets\ActiveForm */


?>

<div class="notice-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form']),
    ]); ?>

    <?=$form->field($model, 'title')->textInput([]) ?>
    
    <?php 
        if ($model->className() == 'common\models\NoticeSystem') {
            echo $form->field($model, 'type')->dropDownList(['0' => '常规','2' => 'H5页面','4' => '产品详情','3' => '文章详情']);
            echo $form->field($model, 'relation')->textInput(['style'=>'width:100%;display:none','placeholder'=>'请输入H5跳转链接'])->label("");
        }
    ?>
    
    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '保存', ['class' => 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<<JS
//对应关联
$(document).on("change",'#noticesystem-type',function(){
    var type = $(this).val();

    switch (type) {
        case ("2"):
            $("#noticesystem-relation").attr('style','display:block');
            $("#noticesystem-relation").prev().text("链接地址");
            $("#noticesystem-relation").attr("placeholder","请输入H5跳转链接");
            break;
        case ("3"):
            $("#noticesystem-relation").attr('style','display:block');
            $("#noticesystem-relation").prev().text("文章ID");
            $("#noticesystem-relation").attr("placeholder","请输入关联的文章ID");
            break;
        case ("4"):
            $("#noticesystem-relation").attr('style','display:block');
            $("#noticesystem-relation").prev().text("产品ID");
            $("#noticesystem-relation").attr("placeholder","请输入关联的产品ID");
            break;
            default:
            $("#noticesystem-relation").attr('style','display:none');
            $("#noticesystem-relation").prev().text("");
            $(".field-noticesystem-relation").children(1).text("");
     }
//     if (type != $model->type){
//         $("#noticesystem-relation").val("");
//     }
});
$(function(){
    var type = $("#noticesystem-type").val();
    switch (type) {
        case ("2"):
            $("#noticesystem-relation").attr('style','display:block');
            $("#noticesystem-relation").prev().text("链接地址");
            break;
        case ("3"):
            $("#noticesystem-relation").attr('style','display:block');
            $("#noticesystem-relation").prev().text("文章ID");
            break;
        case ("4"):
            $("#noticesystem-relation").attr('style','display:block');
            $("#noticesystem-relation").prev().text("产品ID");
            break;
            default:
            $("#noticesystem-relation").attr('style','display:none');
            $("#noticesystem-relation").prev().text("");
            $(".field-noticesystem-relation").children(1).text("");
     }       
});
JS;
$this->registerJs($script);
?>