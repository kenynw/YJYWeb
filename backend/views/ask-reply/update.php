<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AskReply */

$this->title = 'Update Ask Reply: ' . $model->replyid;
$this->params['breadcrumbs'][] = ['label' => 'Ask Replies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->replyid, 'url' => ['view', 'id' => $model->replyid]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="ask-reply-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
