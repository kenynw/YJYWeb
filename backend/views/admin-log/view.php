<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AdminLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '管理员操作记录', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-log-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'route',
            'username',
            [
                'attribute' => 'description',
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_at',
                'value' => date('Y-m-d H:m:s'),
            ],
        ],
    ]) ?>

</div>
