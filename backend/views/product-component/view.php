<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ProductComponent */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Product Components', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-component-view">

    <p>
        <?= Html::a('更新', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'template' => '<tr><th width="10%">{label}</th><td>{value}</td></tr>',
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'risk_grade',
            [
                'attribute' => 'is_active',
                'format' => 'raw',
                'value' => $model->is_active ? '<span class="label label-success">有</span>' : '<span class="label label-default">无</span>',
            ],
            [
                'attribute' => 'is_pox',
                'format' => 'raw',
                'value' => $model->is_pox ? '<span class="label label-success">是</span>' : '<span class="label label-default">否</span>',
            ],
            'component_action',
            'description',
            [
                'format' => 'raw',
                'attribute' => 'created_at',
                'value' => date('Y-m-d H:i:s', $model->created_at),
            ],
        ],
    ]) ?>

</div>
