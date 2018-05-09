<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Ask */

$this->title = 'Update Ask: ' . $model->askid;
$this->params['breadcrumbs'][] = ['label' => 'Asks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->askid, 'url' => ['view', 'id' => $model->askid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ask-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
