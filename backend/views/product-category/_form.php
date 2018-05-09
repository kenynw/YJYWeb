<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\AskQuestion;
use backend\assets\AppAsset;
AppAsset::register($this);

AppAsset::addCss($this,Yii::$app->request->baseUrl."/css/tab.css");

/* @var $this yii\web\View */
/* @var $model common\models\ProductCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <div>
        <div style="clear:both;float:left;width:520px;margin-left:50px">
            <?= $form->field($model, 'parent_id')->dropDownList($parentArr,['style'=>"width:400px;",'disabled'=>$model->isNewRecord ? false : ($model->parent_id == "0" ? "disabled" : false)]) ?>
        
            <?= $form->field($model, 'cate_name')->textInput(['maxlength' => false,'style'=>'width:400px;']) ?>            
            
            <div style="float:left;margin-right:60px;">
                <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['style'=>'width:170px;']) ?>
            </div>
            
            <div style="float:left;">
                <?= $form->field($model, 'sort')->textInput(['style'=>'width:170px;','placeholder'=>'值越小越靠前']) ?>
            </div>
            
            <div class="child_cate" style="display:none">           
                <?= $form->field($model, 'budget', ['labelOptions' => ['label' => '肤质推荐预算(参考文案：0-100，100-200，200-300，300-0)<br><input style="width:50px;margin-top:5px;margin-bottom:5px" id="budget-min">&nbsp;-&nbsp;<input style="width:50px" id="budget-max">&nbsp;&nbsp;<a href="javascript:void 0" id="budget-add">添加</a>','class' => 'control-label']])->textInput(['maxlength' => false,'style'=>'width:400px;display:none']) ?>            
                <div class="plus-tag tagbtn clearfix" id="myTags" style="display: block;">
                <?php 
                    //编辑
                    $budget = $model->budget;
                    $budgetArr = empty($budget) ? '' : explode('，', $budget);
                    if (!empty($budgetArr)) {
                        foreach ($budgetArr as $key=>$val) {
                            echo '<a value="-1" style="color:#333333" title="'.$val.'" href="javascript:void(0);"><span>'.$val.'</span><em></em></a>';
                        }
                    }
                ?>
                </div>           
                
                <div style="float:left;">
                <?= $form->field($model, 'cate_h5_img')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：90x90',
                    ],
                ]) ?>
                </div>
                <div style="float:left;margin-left:20px">
                <?= $form->field($model, 'cate_app_img')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：90x90',
                    ],
                ]) ?>
                </div>
                
                <div class="form-group field-productcategory-question" style="clear:both;">
                    <label class="control-label" for="productcategory-question">推荐问题</label>
                    <?php
                        $questions = $model->isNewRecord ? '' : AskQuestion::find()->where("category_id = $model->id")->orderBy('add_time DESC')->all();
                        if (!empty($questions)) {
                            foreach ($questions as $key => $val) {
                                echo '<input type="text" id="productcategory-question[]" class="form-control question" name="ProductCategory[question][]" value="'.$val->question.'"><span class="questionerror" style="color:red"></span><br>';
                            }
                            if (!empty(count($questions))) {
                                for ($i=0;$i<(3-count($questions));$i++) {
                                    echo '<input type="text" id="productcategory-question[]" class="form-control question" name="ProductCategory[question][]"><span class="questionerror" style="color:red"></span><br>';
                                }
                            }
                        } else {
                            for ($i=0;$i<=2;$i++) {
                                echo '<input type="text" id="productcategory-question[]" class="form-control question" name="ProductCategory[question][]"><span class="questionerror" style="color:red"></span><br>';
                            }
                        }
                    ?>
                    <div class="help-block"></div>
                </div>

        </div>

    </div>

    <div class="form-group" style="clear:both;">
         <?= Html::submitButton('保存', ['class' => 'btn btn-success sub','style' => 'width:100px;margin-left:50px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<<JS
//预算
$('form').on('beforeValidate', function (e) {
    var budget = '';
    $(".plus-tag a").each(function(){
        budget += $(this).attr('title')+'，';
    });
    budget = budget.substring(0,budget.length-1);
    $('#productcategory-budget').val(budget); 
})
$(document).on("click",'#budget-add',function(){
    var min = $('#budget-min').val() == '' ? '0' : $('#budget-min').val();
    var max = $('#budget-max').val() == '' ? '0' : $('#budget-max').val();
    $('.plus-tag').append('<a value="-1" title="'+min+'-'+max+'" href="javascript:void(0);"><span>'+min+'-'+max+'</span><em></em></a>');
})
    
//关闭标签
$(document).on("click",".plus-tag a em",function(){
    $(this).parent('a').remove();
});

//创建一二级分类表单
$(document).on("change",'#productcategory-parent_id',function(){
    var type = $(this).val();
    var relation_id = "#huodongspecialconfig-relation";

    switch (type) {
        case ("0"):
            $(".child_cate").attr('style','display:none');
            break;
            default:
            $(".child_cate").attr('style','display:block');
     }
});

//编辑创建初始一二级分类表单
$(document).ready(function(){
    var parent_id = get_UrlParams("parent_id");
    var id = get_UrlParams("id");
    var input_parent_id = $('#productcategory-parent_id').val();
    if(parent_id == id && id != null) {
        $(".child_cate").attr('style','display:none');
    } else if(parent_id != id && id != '0') {
        $(".child_cate").attr('style','display:block');
    } else if (input_parent_id !='0') {
        $(".child_cate").attr('style','display:block');
    } else {
        $(".child_cate").attr('style','display:none');
    }
})

//问题字符限制
$('.sub').on('click', function () { 
    var fal = 0;
    $(".question").each(function(){ 
        if($(this).val().length > 255){
            $(this).next().html('不能超过255个字符');
            fal = 1;
        } else {
            $(this).next().html('');
        }
    });   
    if(fal == 1){
        return false;
        fal = 0;
        $(':submit').removeAttr('disabled').removeClass('disabled');
    }else{
        return true;
        $(':submit').attr('disabled', true).addClass('disabled');
    }
});
JS;
$this->registerJs($script);
?>