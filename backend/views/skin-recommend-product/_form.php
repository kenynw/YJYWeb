<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\ProductCategory;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\SkinRecommendProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="skin-recommend-product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'skin_id')->dropDownList($skinList,['prompt'=>'请选择']) ?>

    <?= $form->field($model, 'cate_id')->dropDownList($cateList,['prompt'=>'请选择']) ?>

    <?= $form->field($model, 'product_id')->widget(Select2::classname(), [
        'name' => 'productSelect',
        'options' => ['placeholder' => '请输入产品名称 ...'],
        'pluginOptions' => [
            'placeholder' => 'Waiting...',
            'language'=>"zh-CN",
            'minimumInputLength'=> 1,
            'allowClear' => true,
            'ajax' => [
                'url' => '/ranking/search-product',
                'dataType' => 'json',
                'cache' => true,
                'data' => new JsExpression('function(params) { return {q:params.term}; }'),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ],
        'pluginEvents' => [
            "select2:select" => "function(){        
                    
            }",        
        ]
    ])->label('产品名称(已上架)') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '保存' : '确定', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
