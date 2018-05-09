<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\SkinBaike */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="skin-baike-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php //echo $form->field($model, 'skin_id')->dropDownList( $skinList,['prompt'=>'请选择'])->label("针对肤质") ?>

    <?= $form->field($model, 'question')->textarea(['rows' => '3']) ?>
    
    <?= $form->field($model, 'answer')->textarea(['rows' => '3']) ?>
    
    <?= $form->field($model, 'shortanswer')->textarea(['rows' => '3']) ?>
    
    <?=$form->field($model, 'picture')->widget('common\widgets\file_upload\FileUpload',[
            'config'=>[
                'domain_url' => Yii::$app->params['uploadsUrl'],
                'explain' => '推荐尺寸：648x405',
            ],
            ]);?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
