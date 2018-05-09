<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ArticleCategorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="article-category-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id')->textInput(['style' => "width:120px;"])  ?>

    <?= $form->field($model, 'cate_name')->textInput(['style' => "width:120px;"]) ?>

    <?= $form->field($model, 'status')->textInput(['style' => "width:120px;"]) ?>

    <?= $form->field($model, 'created_at')->textInput(['style' => "width:120px;"]) ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
