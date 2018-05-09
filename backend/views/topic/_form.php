<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Topic */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="topic-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput([]) ?>

    <?= $form->field($model, 'desc')->textarea(['rows' => 4]) ?>
    
    <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架']) ?>

    <div style="float:left;padding-left:8%;">
            <?= $form->field($model, 'picture')->widget('common\widgets\file_upload\FileUpload',[
                'config'=>[
                    'domain_url' => Yii::$app->params['uploadsUrl'],
                    'explain' => '推荐尺寸：',
                ],
            ]) ?>
    </div>
    
    <div style="float:left;padding-left:8%;">
            <?= $form->field($model, 'share_pic')->widget('common\widgets\file_upload\FileUpload',[
                'config'=>[
                    'domain_url' => Yii::$app->params['uploadsUrl'],
                    'explain' => '推荐尺寸：',
                ],
            ]) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '保存' : '确认', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
