<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Ask */

$this->title = 'Create Ask';
$this->params['breadcrumbs'][] = ['label' => 'Asks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ask-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
