<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Brand */

$this->title = '添加品牌页';
$this->params['breadcrumbs'][] = ['label' => '品牌列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
