<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Topic */

$this->title = 'Update Topic: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Topics', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="topic-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
