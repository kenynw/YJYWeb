<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AppVersion */

$this->title = '更新 版本设置: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'App Versions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-version-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
