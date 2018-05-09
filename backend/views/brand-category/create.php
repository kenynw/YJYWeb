<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\BrandCategory */

$this->title = 'Create Brand Category';
$this->params['breadcrumbs'][] = ['label' => 'Brand Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
