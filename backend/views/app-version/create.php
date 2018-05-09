<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\AppVersion */

$this->title = '创建 版本设置';
$this->params['breadcrumbs'][] = ['label' => 'App Versions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-version-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
