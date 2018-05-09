<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AppVersion */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'App Versions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-version-view">

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

    <?php
    if($model->type == 1){
        $downloadUrl =  Html::a(Yii::$app->params['frontendUrl'] . $model->downloadUrl,Yii::$app->params['frontendUrl'] . $model->downloadUrl);
    }else{
        $downloadUrl =  Html::a($model->downloadUrl,$model->downloadUrl);
    }
    ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'type',
                'value'     => $model->type == 1 ? 'android' : 'ios',
            ],
            [
                'attribute' => 'content',
                'format'    => 'raw',
            ],
            'number',
            [
                'attribute' => 'status',
                'value'     => $model->status == 1 ? '是' : '否',
            ],
            [
                'format'    => 'raw',
                'attribute' => 'downloadUrl',
                'value'     => $downloadUrl
            ],

            //'isMust',
            //'creater_id',
            'create_time:datetime',
            //'update_time:datetime',
        ],
    ]) ?>

</div>
