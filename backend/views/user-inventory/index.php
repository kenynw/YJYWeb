<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\UserInventory;
use common\models\UserInventorySearch;
use common\functions\Tools;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserInventorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


//用户详情我的清单列表
if (!empty($user_id)) {
    $search = Yii::$app->request->queryParams;
    $search['UserInventorySearch']['user_id'] = $user_id;
    $searchModel = new UserInventorySearch();
    $dataProvider = $searchModel->search($search);
}
?>
<div class="user-inventory-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "id" => 'grid6',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'title',
                'value' => function ($model) {
                    return Tools::userTextDecode($model->title);
                }
            ],
            [
                'label' => '清单产品',
                'format' => 'raw',
                'value' => function($model) {
                    $return = UserInventory::getProduct($model->id);
                    return empty($return) ? '' : Html::a('查看',['user-inventory-product/index','UserInventorySearch[invent_id]'=>$model->id], ['class' => 'btn btn-success btn-xs noself']);
                }
            ],
            'add_time',
        ],
    ]); ?>
</div>
