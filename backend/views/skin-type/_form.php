<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\SkinType */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="skin-type-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <div>
    
    <div style="margin-bottom:20px">
        <label class="control-label" for="skintype-name">肤质类型</label>&nbsp;&nbsp;&nbsp;&nbsp;<span><?=$model->name ?></span>
    </div>
    
    <?= $form->field($model, 'name_en')->textInput() ?>
    
    <div style="clear:both;"><label class="control-label" for="skintype-name">分数</label></div>
    <div style="float:left;">        
        <?php echo $form->field($model, 'min')->textInput(['style'=>'width:80px;'])->label('最小值') ?>
    </div>
    <div style="margin-top: 30px">&nbsp;--&nbsp;</div>
    <div style="float:left;margin-top: -50px;margin-left:15px">
        <?php echo $form->field($model, 'max')->textInput(['style'=>'width:80px;'])->label('最大值') ?>
    </div>

    <div style="clear: both">
    
        <?= $form->field($model, 'unscramble')->textarea(['rows' => 6]) ?>
    
        <div class="form-group">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
