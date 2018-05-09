<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Banner */

$this->title = 'banner创建';
$this->params['breadcrumbs'][] = ['label' => 'Banners', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="banner-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
