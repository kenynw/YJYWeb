<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Ranking */

$this->title = '添加排行榜';
$this->params['breadcrumbs'][] = ['label' => '分类排行榜设置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ranking-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
