<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Banner */

$this->title = 'banner详情---'.$model->title;
$this->params['breadcrumbs'][] = ['label' => 'Banners', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$time = strtotime(date("Y-m-d",time()));
$status = $model->status == 0 ? '上线' : '下线';
?>
<div class="banner-view">

     <!-- <h1><?= Html::encode($this->title) ?></h1>  -->

    <p>
        <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a($model->status == 0 ? '上线' : '下线', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => "确定要将banner".$status."吗？",
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => Html::a($model->url,$model->url),
            ],
            [
                'attribute' => 'type',
                'format' => 'raw',
                'value' => \Yii::$app->params['bannerType'][$model->type],
            ],
            [
                'attribute' => 'img',
                'format' => 'raw',
                'value' => Html::img(Yii::$app->params['uploadsUrl'] . $model->img,['width' => '100','height' => '50'])
            ],
            [  
                'attribute' => 'sort_id',
                'value' => $model->sort_id,  
            ],
            [
                'format' => 'raw',
                'attribute' => 'start_time',
                'value' => date('Y-m-d H:i:s', $model->start_time),
            ],
            [
                'format' => 'raw',
                'attribute' => 'end_time',
                'value' => date('Y-m-d H:i:s', $model->end_time),
            ],
            // [
            //     'format'    => 'raw',
            //     'attribute' => 'created_at',
            //     'value'     => date('Y-m-d H:i:s', $model->created_at),
            // ],
            // [
            //     'format'    => 'raw',
            //     'attribute' => 'updated_at',
            //     'value'     => date('Y-m-d H:i:s', $model->updated_at),
            // ],
            // 'id',
            // 'title',
            // 'url:url',
            // 'img',
            // 'type',
            // 'sort_id',
            // 'start_time',
            // 'end_time',
            // 'created_at',
            // 'updated_at',
        ],
    ]) ?>

</div>
