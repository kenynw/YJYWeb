<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Advertisement */

$this->title = '新增广告';
$this->params['breadcrumbs'][] = ['label' => '广告列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="advertisement-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
