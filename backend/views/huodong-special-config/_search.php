<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\HuodongSpecialConfigSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="huodong-special-config-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'prize') ?>

    <?= $form->field($model, 'prize_num') ?>

    <?= $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'starttime') ?>

    <?php // echo $form->field($model, 'endtime') ?>

    <?php // echo $form->field($model, 'performer') ?>

    <?php // echo $form->field($model, 'addtime') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
