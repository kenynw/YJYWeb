<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Startup */

$this->title = 'Create Startup';
$this->params['breadcrumbs'][] = ['label' => 'Startups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="startup-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
