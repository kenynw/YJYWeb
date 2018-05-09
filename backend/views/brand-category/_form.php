<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\BrandCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="brand-category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['style'=>'width:250px;']) ?>

    <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['style'=>'width:170px;']) ?>
    
    <?= $form->field($model, 'sort')->textInput(['style'=>'width:150px','placeholder'=>'越小越前']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
