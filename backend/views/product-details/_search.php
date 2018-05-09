<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductDetailsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-details-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'product_name') ?>

    <?= $form->field($model, 'brand') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'is_recommend') ?>

    <?php // echo $form->field($model, 'cate_id') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'form') ?>

    <?php // echo $form->field($model, 'star') ?>

    <?php // echo $form->field($model, 'standard_number') ?>

    <?php // echo $form->field($model, 'product_country') ?>

    <?php // echo $form->field($model, 'product_date') ?>

    <?php // echo $form->field($model, 'product_company') ?>

    <?php // echo $form->field($model, 'en_product_company') ?>

    <?php // echo $form->field($model, 'component_id') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
