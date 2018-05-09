<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\HuodongSpecialConfig */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="huodong-special-config-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-id', 'enableAjaxValidation' => true,'validationUrl' => Url::toRoute(['validate-form']),
    ]); ?>

    <div>
        <div style="float:left;width:100%;">
            <?= $form->field($model, 'name')->textInput(['maxlength' => false,'style'=>'width:100%']) ?>

            <div style="float:left;margin-right:3%;width:45%;">
                <?= $form->field($model, 'prize')->textInput(['maxlength' => false,'style'=>'width:100%']) ?>
            </div>
            <div style="float:left;margin-left:2%;width:15%;">
                <?= $form->field($model, 'prize_num')->textInput(['maxlength' => false,'style'=>'width:100%']) ?>
            </div>
            <div style="float:left;margin-left:3%;width:15%;">
                <?= $form->field($model, 're_number')->textInput(['maxlength' => false,'style'=>'width:100%']) ?>
            </div>
            <div style="float:left;margin-left:2%;width:15%;">
                <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架']) ?>
            </div>
            <div style="float:left;width:45%;clear:both;">
                <?= $form->field($model, 'type')->dropDownList(['0'=>'H5页面','1'=>'产品','2'=>'文章']) ?>
            </div>
            <div style="float:left;margin-left:5%;width:50%;">
                <?=$form->field($model, 'relation')->textInput(['style'=>'width:100%;','placeholder'=>'请输入H5跳转链接'])->label("链接地址"); ?>
            </div>
            <div style="clear:both;">
                <?= $form->field($model, 'notice')->textarea(['rows' => '3']) ?>
            </div>
            <div style="clear:both;">
                <?= $form->field($model, 'picture')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：640x300',
                    ],
                ]) ?>
            </div>
            
            <div style="float:left;margin-right:7%;width:45%;">
                <?=$form->field($model, 'starttime')->textInput(['id' => 'start_time', 'style'=>'width:100%;','onClick'=>"WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})",'value'=>$model->isNewRecord ? '' : date('Y-m-d H:i:s',$model->starttime)]) ?>
            </div>
            <div style="float:left;width:45%;">
                <?= $form->field($model, 'endtime')->textInput(['id' => 'end_time',  'style'=>'width:100%;','onClick'=>"WdatePicker({el:this,dateFmt:'yyyy-MM-dd HH:mm:ss'})",'value'=>$model->isNewRecord ? '' : date('Y-m-d H:i:s',$model->endtime)]) ?>
            </div>

        </div>

    </div>
    
    <div class="form-group" style="clear:both;">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '修改', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?=Html::jsFile('@web/js/My97DatePicker/WdatePicker.js');?>

<?php
$script = <<<JS
//对应关联
$(document).on("change",'#huodongspecialconfig-type',function(){
    var type = $(this).val();
    var relation_id = "#huodongspecialconfig-relation";

    switch (type) {
        case ("0"):
            $(relation_id).prev().text("链接地址");
            $(relation_id).attr("placeholder","请输入H5跳转链接");
            break;
        case ("1"):
            $(relation_id).prev().text("产品ID");
            $(relation_id).attr("placeholder","请输入关联的产品ID");
            break;
        case ("2"):
            $(relation_id).prev().text("文章ID");
            $(relation_id).attr("placeholder","请输入关联的文章ID");
            break;
            default:
            $(relation_id).prev().text("链接地址");
            $(relation_id).attr("placeholder","请输入H5跳转链接");
     }
});

JS;
$this->registerJs($script); ?>