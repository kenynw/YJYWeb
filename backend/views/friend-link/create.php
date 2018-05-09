<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\FriendLink */

$this->title = '添加友链';
$this->params['breadcrumbs'][] = ['label' => 'Friend Links', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="friend-link-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
