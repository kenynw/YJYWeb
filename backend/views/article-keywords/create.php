<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ArticleKeywords */

$this->title = 'Create Article Keywords';
$this->params['breadcrumbs'][] = ['label' => 'Article Keywords', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-keywords-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
