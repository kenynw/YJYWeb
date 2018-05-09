<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SkinBaike */

$this->title = 'Update Skin Baike: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Skin Baikes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="skin-baike-update">

    <?= $this->render('_form', [
        'model' => $model,
        'skinList' => $skinList
    ]) ?>

</div>
