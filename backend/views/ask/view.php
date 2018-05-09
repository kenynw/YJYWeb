<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Ask */

$this->title = $model->askid;
$this->params['breadcrumbs'][] = ['label' => 'Asks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ask-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->askid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->askid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'askid',
            'subject',
            'content',
            'username',
            'user_id',
            'status',
            'product_id',
            'product_name',
            'add_time:datetime',
        ],
    ]) ?>

</div>
