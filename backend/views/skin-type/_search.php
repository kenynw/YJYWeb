<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\SkinTypeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="skin-type-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'skin_id') ?>

    <?= $form->field($model, 'min') ?>

    <?= $form->field($model, 'max') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'unscramble') ?>

    <?php // echo $form->field($model, 'order') ?>

    <?php // echo $form->field($model, 'add_time') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
