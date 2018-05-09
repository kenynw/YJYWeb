<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\base\Object;
use common\models\UserAccountSearch;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserAccountSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = 'User Accounts';
// $this->params['breadcrumbs'][] = $this->title;

$search = Yii::$app->request->queryParams;
$search['UserAccountSearch']['user_id'] = $user_id;
$searchModel = new UserAccountSearch();
$dataProvider = $searchModel->search($search);

?>
<?php Pjax::begin([
    'enablePushState' => false,
    'timeout'         => 10000,
])?>
<div class="user-account-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => false,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'user.username',
            'content',
            [
                'attribute' => 'created_at',
                'value'     => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
            ],
            'money',
        ],
    ]); ?>
</div>
<?php Pjax::end()?>