<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ArticleKeywords */

$this->title = 'Update Article Keywords: ' . $model->keyword;
$this->params['breadcrumbs'][] = ['label' => 'Article Keywords', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->keyword, 'url' => ['view', 'id' => $model->keyword]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="article-keywords-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
