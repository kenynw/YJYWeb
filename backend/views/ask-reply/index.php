<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AskReplySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ask Replies';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ask-reply-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Ask Reply', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'replyid',
            'askid',
            'reply',
            'username',
            'user_id',
            // 'add_time',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
