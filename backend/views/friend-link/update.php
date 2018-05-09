<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\FriendLink */

$this->title = '编辑友链: ' . $model->link_id;
$this->params['breadcrumbs'][] = ['label' => 'Friend Links', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->link_id, 'url' => ['view', 'id' => $model->link_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="friend-link-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
