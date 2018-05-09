<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

$this->title = '修改 账号: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = '修改账号';

?>


<div class="admin-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['disabled' => 'true']) ?>
    
    <?= $form->field($model, 'status')->dropDownList([0 => '禁用', 10 => '启用',]) ?>

    <?php
        echo $form->field($model, 'connect_user_id')->widget(Select2::classname(), [
            'data' => $userList,
            'options' => ['placeholder' => '请选择 ...'],
        ]);
    ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
