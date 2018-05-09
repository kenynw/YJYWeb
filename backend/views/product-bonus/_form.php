<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\ProductBonus */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-bonus-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form','id'=>$model->isNewRecord ? '' : $model->id]),
    ]); ?>
    
    <?php $model->type = $model->isNewRecord ? '' : empty($model->bonus_link) ? '1' : '0' ?> 
    <?= $form->field($model, 'type')->dropDownList(['0'=>'有','1'=>'无'],['options'=>['0'=>['disabled'=>$model->isNewRecord ? 1 : $model->type == '0' ? false : true],'1'=>['disabled'=>$model->isNewRecord ? 1 : $model->type == '1' ? false : true]]])->label('有无优惠券') ?>

    <div style="display:block"  class = "no_bonus">    
        <?= $form->field($model, 'goods_link')->textInput([]) ?>
    </div>
    
    <?= $form->field($model, 'goods_id')->textInput([]) ?>
    
    <div style="display:block" class = "has_bonus">
        <?= $form->field($model, 'bonus_link')->textInput([]) ?>
    
        <?= $form->field($model, 'price')->textInput([]) ?>
    
        <?=$form->field($model, 'start_date')->textInput(['id' => 'start_date', 'value' => $model->start_date?$model->start_date:date('Y-m-d'), 'style'=>'width:100%;']) ?>
    
        <?= $form->field($model, 'end_date')->textInput(['id' => 'end_date', 'value' => $model->end_date?$model->end_date:date('Y-m-d',time()+3600*24), 'style'=>'width:100%;']) ?>
    </div>
    <br>
    
    <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架']) ?>
    
    <?= $form->field($model, 'sort')->textInput(['placeholder' => '排序值越大，排名越靠前']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<<JS
//时间
$('#start_date').datepicker({
    autoclose: true,
    format : 'yyyy-mm-dd',
    'language' : 'zh-CN',
});
$('#end_date').datepicker({
    autoclose: true,
    format : 'yyyy-mm-dd',
    'language' : 'zh-CN',
});
    

//改变筛选有无优惠券
$(document).on("change",'#productbonus-type',function(){
    var type = $(this).val();
    
    switch (type) {
        case ("0"):
            $(".has_bonus").attr('style','display:block');
            $(".no_bonus").attr('style','display:block');
            break;
            default:
            $(".no_bonus").attr('style','display:block');
            $(".has_bonus").attr('style','display:none');
     }
});

//编辑改变筛选有无优惠券
$(document).ready(function(){
    var type = $("#productbonus-type").val();
    
    switch (type) {
        case ("0"):
            $(".has_bonus").attr('style','display:block');
            $(".no_bonus").attr('style','display:block');
            break;
            default:
            $(".no_bonus").attr('style','display:block');
            $(".has_bonus").attr('style','display:none');
     }
})

JS;
$this->registerJs($script);
?>