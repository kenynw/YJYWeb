<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\UserProductSearch;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


//用户详情我在用的列表
if (!empty($user_id)) {
    $search = Yii::$app->request->queryParams;
    $search['UserProductSearch']['user_id'] = $user_id;
    $searchModel = new UserProductSearch();
    $dataProvider = $searchModel->search($search);
}
?>
<div class="user-product-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "id" => 'grid5',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'brand_name',
            [
                'attribute' => 'product',
                'format' => 'raw',
                'value' => function($model){
                    return empty($model->product_id) ? $model->product : Html::a($model->product, ['product-details/view','id'=> $model->product_id]);
                },
            ],
            [
                'attribute' => 'is_seal',
                'format' => 'raw',
                'value' => function($model){
                    $return = '其他';
                    $today = strtotime(date('Y-m-d',time()));
                    $quality_time = $model->quality_time > 0 ? strtotime("+$model->quality_time Month",$model->seal_time) : strtotime("+$model->days Day",$model->seal_time);
                    if (($model->overdue_time < $today || $quality_time < $today) && !empty($model->is_seal)) {
                        $return = '已过期';
                    }
                    if ($model->overdue_time >= $today && $quality_time >= $today && !empty($model->is_seal)) {
                        $return = '已开封';
                    }
                    if (empty($model->is_seal)) {
                        $return = '未开封';
                    }
                    return $return;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_seal',['1' => '未开封','2' => '已开封','3' => '已过期'],
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'overdue_time',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d', $model->overdue_time);
                },
            ],
            [
                'attribute' => 'seal_time',
                'format' => 'raw',
                'value' => function($model){
                    return !empty($model->is_seal) ? date('Y-m-d', $model->seal_time) : '';
                },
            ],
            [
                'attribute' => 'quality_time',
                'format' => 'raw',
                'value' => function($model){
                    return !empty($model->is_seal) ? empty($model->quality_time) ? $model->days.'天' : $model->quality_time.'个月' : '';
                },
            ],
            [
                'attribute' => 'add_time',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->add_time);
                },
            ],
        ],
    ]); ?>
</div>
