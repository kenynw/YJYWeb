<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AskReply */

$this->title = $model->replyid;
$this->params['breadcrumbs'][] = ['label' => 'Ask Replies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ask-reply-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->replyid], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->replyid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'replyid',
            'askid',
            'reply',
            'username',
            'user_id',
            'add_time',
        ],
    ]) ?>

</div>
