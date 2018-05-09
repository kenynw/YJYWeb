<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Brand;
use backend\models\CommonFun;

/* @var $this yii\web\View */
/* @var $model common\models\Brand */

$this->title = empty($model->name) ? $model->ename : $model->name;
$this->params['breadcrumbs'][] = ['label' => '品牌列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
.table td {border-right:1px solid #ECF0F5}
</style>
<div class="brand-view">

    <p>
        <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <table  class="table" style="border:1px solid #ECF0F5">
        <tr><th rowspan="4" width="75px">品牌图</th><td rowspan="4" width="20%"><img src="<?=Yii::$app->params['uploadsUrl'].$model->img ?>" width="218" height="146"></td>
            <th>中文名</th><td><?=$model->name ?></td>
            <th>状态</th><td><?=empty($model->status) ? '下架' : '上架' ?></td>
        </tr>
        <tr>
            <th>英文名</th><td><?=$model->ename ?></td>
            <th>上榜产品数</th><td><?=Html::a(Brand::getProduct($model->id,'2'), ['product-details/index','ProductDetailsSearch[brand_id]'=> $model->id,'ProductDetailsSearch[is_top]'=> 1,'btype'=>'brand']) ?></td>
        </tr>
        <tr>
            <th>别名</th><td><?=$model->alias ?></td>
            <th>总产品数</th><td><?=Html::a(Brand::getProduct($model->id), ['product-details/index','ProductDetailsSearch[brand_id]'=> $model->id,'btype'=>'brand']); ?></td>
        </tr>
        <tr>
            <th>热度</th><td><?=$model->hot ?></td>
            <th>所属分类</th><td><?=empty($model->brandCategory->name) ? '' : $model->brandCategory->name ?></td>
        </tr>
        <tr>           
            <th>是否抓取</th><td><?=empty($model->is_auto) ? '否' : '是' ?></td>
            <th>是否推荐</th><td><?=empty($model->is_recommend) ? '默认' : '推荐' ?></td>
            <th>所属品牌</th><td><?=$model->parent_id ?></td>
        </tr>
<!--         <tr> -->
            <!--<th></th><td><?php //echo empty($model->is_recommend) ? '默认' : '推荐' ?></td>-->
<!--             <th></th><td></td> -->
<!--         </tr> -->
    </table>
    <br>
    <label>官方旗舰店购买渠道</label>
        <div style="border:1px solid #ECF0F5;width:100%;padding:10px;line-height:50px;word-break: break-all">
                淘宝：<?=empty($model['link_tb']) ? '无' : $model['link_tb'] ?><br>
                京东：<?=empty($model['link_jd']) ? '无' : $model['link_jd']  ?><br>
        </div>
    <br>
    <label>品牌描述</label>  
    <div style="border:1px solid #ECF0F5;width:70%;padding:10px;line-height:50px">
        <?=empty($model->description) ? '无' : $model->description ?>
    </div>
    <br>
    <label>操作记录</label>  
    <?= $this->render('/admin-log-view/index', [
        'relateId' => $model->id,
        'action' => Yii::$app->controller->id,
    ]) ?>
</div>
