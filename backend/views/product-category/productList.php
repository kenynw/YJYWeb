<?php

use yii\helpers\Html;

$this->title = '分类产品---'.$category->cate_name;
$this->params['breadcrumbs'][] = ['label' => '分类列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tags-index">    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?= $this->render('/product-details/index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]) ?>
</div>
