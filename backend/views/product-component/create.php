<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ProductComponent */

$this->title = 'Create Product Component';
$this->params['breadcrumbs'][] = ['label' => 'Product Components', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-component-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
