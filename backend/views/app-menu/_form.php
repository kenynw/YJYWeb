<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AppMenu */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="app-menu-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput([]) ?>

    <?= $form->field($model, 'subtitle')->textInput([]) ?>

    <?= $form->field($model, 'sort')->textInput(['placeholder' => "值越小越靠前"]) ?>

    <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架']) ?>
    
    <?= $form->field($model, 'type')->dropDownList(['1' => '产品库','2' => '我在用的','3' => '今天买什么','4' => 'AVI','5' => '批号查询','6' => '福利']) ?>

    <?= $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
        'config'=>[
            'domain_url' => Yii::$app->params['uploadsUrl'],
            'explain' => '推荐尺寸：100*100',
        ],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
