<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\SkinType */

$this->title = '编辑';
$this->params['breadcrumbs'][] = ['label' => 'Skin Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->skin_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="skin-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
