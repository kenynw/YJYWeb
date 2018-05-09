<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AskReply */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ask-reply-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'askid')->textInput([]) ?>

    <?= $form->field($model, 'reply')->textInput([]) ?>

    <?= $form->field($model, 'username')->textInput([]) ?>

    <?= $form->field($model, 'user_id')->textInput([]) ?>

    <?= $form->field($model, 'add_time')->textInput([]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
