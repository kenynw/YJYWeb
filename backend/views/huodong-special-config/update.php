<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\HuodongSpecialConfig */

$this->title = '修改活动: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => '活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="huodong-special-config-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
