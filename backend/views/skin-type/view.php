<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\SkinType */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Skin Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="skin-type-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->skin_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->skin_id], [
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
            'skin_id',
            'min',
            'max',
            'name',
            'unscramble',
            'order',
            'add_time',
        ],
    ]) ?>

</div>
