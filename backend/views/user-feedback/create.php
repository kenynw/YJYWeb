<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\UserFeedback */

$this->title = 'Create User Feedback';
$this->params['breadcrumbs'][] = ['label' => 'User Feedbacks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-feedback-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
