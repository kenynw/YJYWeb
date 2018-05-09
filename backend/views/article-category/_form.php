<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ArticleCategory */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="article-category-form">

    <?php $form = ActiveForm::begin(); ?>
    <div>
    <div style="display: none;">
        <?= $form->field($model, 'parent_id')->textInput(['value' => $_GET['parent_id']]) ?>
    </div>
    <div style="float:left;">
        <?= $form->field($model, 'cate_img')->widget('common\widgets\file_upload\FileUpload',[
            'config'=>[
                'domain_url' => Yii::$app->params['uploadsUrl'],
                'explain' => '<b>推荐尺寸：</b>672*423',
            ],
        ]) ?>
    </div>
    <div style="float:left;margin-left:70px">
        <?= $form->field($model, 'cate_name')->textInput(['style'=>'width:150px']) ?>
        <br>
        <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['style'=>'width:120px;']) ?>
        <br>
        <?= $form->field($model, 'order')->textInput(['style'=>'width:150px','placeholder'=>'越小越前']) ?>
    </div>
    
    <div style="clear:both;">
        <?= $form->field($model, 'describe')->textarea(['rows' => 3]) ?> 
    </div>
    </div> 

    <div class="form-group" style="clear:both;">
        <?= Html::submitButton($model->isNewRecord ? '确认添加' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
