<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserFeedback */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-feedback-form">

    <?php $form = ActiveForm::begin(); ?>
    
    <?= $form->field($model, 'feedback')->textarea(['rows' => 6])->label('') ?>
    
    <?= $form->field($model, 'picture')->widget('common\widgets\file_upload\FileUpload',[
        'config'=>[
            'domain_url' => Yii::$app->params['uploadsUrl'],
            'explain' => '推荐尺寸：630x393',
        ],
    ]);?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '保存' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
