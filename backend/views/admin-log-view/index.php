<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\base\Object;
use common\models\AdminLogViewSearch;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AdminLogViewSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if ($action && $relateId) {
    $searchModel = new AdminLogViewSearch();
    $params = Yii::$app->request->queryParams;
    $params['AdminLogViewSearch']['action'] = $action;
    $params['AdminLogViewSearch']['relate_id'] = $relateId;
    $dataProvider = $searchModel->search($params);
}
?>
<div class="admin-log-view-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '5%']
            ],

            [
                'attribute' => 'username',
                'options' => ['width' => '15%'],
                'value' => function ($model) {
                    return $model->username;
                },
                'filter' => false
            ],
            [
                'attribute' => 'description',
                'value' => function ($model) use ($action) {
                    return $model->description;
                },
                'filter' => $action == 'brand' ? false : Html::activeDropDownList($searchModel,'description',['图片' => '图片','返利链接' => '返利链接','评论' => '评论'],['prompt' => '所有'])
            ],
            [
                'attribute' => 'created_at',
                'options' => ['width' => '15%'],
                'value'     => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => false
            ],
        ],
    ]); ?>
</div>
