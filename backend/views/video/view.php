<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Video;

/* @var $this yii\web\View */
/* @var $model common\models\Video */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => '站内视频列表', 'url' => ['index1']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
.table td {border-right:1px solid #ECF0F5}
</style>
<div class="video-view">
    <p>
        <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <table  class="table" style="border:1px solid #ECF0F5">
        <tr><th rowspan="2" width="10px">视频</th><td rowspan="2" width="20%"><video src="<?=Yii::$app->params['uploadsUrl'].$model->video ?>" width="218" height="146" controls="controls"></td>
        <th rowspan="2" width="10px">封面图</th><td rowspan="2" width="20%"><img src="<?=Yii::$app->params['uploadsUrl'].$model->thumb_img ?>" width="218" height="146" controls="controls"></td>
            <th width="5px">视频标题</th><td><?=$model->title ?></td>
            <?php $product_num = empty($model->product_id) ? '0' : count(explode(',', $model->product_id));
                  $title = empty($model->product_id) ? '' : '<p align="left">'. Video::getProductStr($model->id).'</p>';?>
            <th width="5px">相关产品</th><td><?=empty($model->product_id) ? '无' : Html::a($product_num, 'javascript:void(0)',['title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right','data-html'=>'true']); ?></td>
        </tr>
        <tr>
            <th>状态</th><td width="20%"><?=empty($model->status) ? '下架' : '上架' ?></td>
            <th width="5px">视频时长</th><td width="40px"><?=$model->duration ?></td>
        </tr>
        <tr>
            <th>说明</th><td colspan="7" style="padding: 15px"><?=$model->desc ?></td>
        </tr>
    </table>
</div>

<?= $this->render('_comment', [
    'data_id' => $model->id,
    'type' => 3,
    'jump_url' => Yii::$app->request->url,
]) ?>