<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AskReplySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ask-reply-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'replyid') ?>

    <?= $form->field($model, 'askid') ?>

    <?= $form->field($model, 'reply') ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'add_time') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
