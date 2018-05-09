<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Startup */

$this->title = 'Update Startup: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Startups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="startup-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
