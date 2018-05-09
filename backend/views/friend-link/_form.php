<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\FriendLink */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="friend-link-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'link_name')->textInput([]) ?>

    <?= $form->field($model, 'link_url')->textInput([]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
