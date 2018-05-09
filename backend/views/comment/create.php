<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Comment */

$this->title = 'Create Comment';
$this->params['breadcrumbs'][] = ['label' => 'Comments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-create">

    <?= $this->render('_form', [
        'model' => $model,
        'shadowList' => $shadowList,
    ]) ?>

</div>
