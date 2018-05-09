<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\AskReply */

$this->title = 'Create Ask Reply';
$this->params['breadcrumbs'][] = ['label' => 'Ask Replies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ask-reply-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
