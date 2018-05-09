<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\CommonFun;
use common\models\BrandCategory;
use yii\base\Object;
use kartik\select2\Select2;
use common\models\Brand;
use yii\web\JsExpression;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Brand */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
.table td {border-right:1px solid #ECF0F5}
.table tr:nth-of-type(1) th {border-right:1px solid #ECF0F5}
.table input {width:250px}
</style>

<div class="brand-form">

    <?php $form = ActiveForm::begin(); ?>

    <table class="table" style="border:1px solid #ECF0F5">
        <tr>
            <td rowspan="3" width="20%"><?php echo $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
                                                'config'=>[
                                                    'domain_url' => Yii::$app->params['uploadsUrl'],
                                                    'explain' => '<b>推荐尺寸：</b>152x152',
                                                ],
                           ]) ?></td>
            <td width="25%"><?= $form->field($model, 'name')->textInput([]) ?></td>
            <td><?= $form->field($model, 'hot')->textInput([]) ?></td>
        </tr>
        <tr>
            <td><?= $form->field($model, 'ename')->textInput([]) ?></td>
            <td><?= $form->field($model, 'cate_id')->dropDownList(CommonFun::getKeyValArr(new BrandCategory(), 'id', 'name'),['style'=>'width:170px;','prompt'=>'--- 请选择 ---']) ?><?php //echo $form->field($model, 'is_recommend')->dropDownList(['0'=>'默认','1'=>'推荐'],['style'=>'width:170px;']) ?></td>
        </tr>
        <tr>
            <td><?= $form->field($model, 'alias')->textInput() ?></td>
            <td><?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['style'=>'width:170px;']) ?></td>
        </tr>
        <tr>
            <td><?= $form->field($model, 'is_auto')->dropDownList(['0'=>'不抓取','1'=>'抓取'],['style'=>'width:170px;']) ?></td>
            <td><?= $form->field($model, 'is_recommend')->dropDownList(['0'=>'默认','1'=>'已推荐'],['style'=>'width:170px;']) ?></td>
            <td><?= $form->field($model, 'parent_id')->widget(Select2::classname(), [
                                                              'options' => ['placeholder' => '请输入品牌名称 ...'],
                                                              'data' => CommonFun::getKeyValArr(new Brand(), 'id', 'name'),
                                                              'pluginOptions' => [
                                                                   'placeholder' => 'Waiting...',
                                                                   'language'=>"zh-CN",
                                                                   'minimumInputLength'=> 1,
                                                                   'allowClear' => true,
                                                                   'ajax' => [
                                                                       'url' => '/product-details/search-brand?where=parent_id=0',
                                                                       'dataType' => 'json',
                                                                       'cache' => true,
                                                                       'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                                                                   ],
                                                                   'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                                                   'templateResult' => new JsExpression('function(res) { return res.text; }'),
                                                                   'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                                                               ],
                                                          ])->label('一级品牌（二级品牌必填，不填则默认为一级品牌）');?>
            </td>
        </tr>
    </table>

    <label>官方购买渠道</label>
    <div style="float:left;width:100%;padding:10px;">
    <div style="float:left;margin-right:60px;">
    <?= $form->field($model, 'link_tb')->textInput(['placeholder' => '此处输入官方旗舰店的推广链接','style'=>'width:600px;']) ?>  
    </div>  
    <div style="float:left;margin-right:60px;"> 
    <?= $form->field($model, 'link_jd')->textInput(['placeholder' => '此处输入官方旗舰店的推广链接','style'=>'width:600px;']) ?>   
    </div>   
    </div>
    
    <?= $form->field($model, 'description')->textarea(['rows' => 6,'style'=>'width:79%;']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认添加' : '保存', ['class' => 'btn btn-success','style' => 'margin-left:14px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
