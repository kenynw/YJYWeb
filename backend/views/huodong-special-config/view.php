<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\HuodongSpecialConfig */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Huodong Special Configs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="huodong-special-config-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'name',
            'prize',
            'prize_num',
            'status',
            'starttime:datetime',
            'endtime:datetime',
            'performer',
            'addtime:datetime',
        ],
    ]) ?>

</div>
