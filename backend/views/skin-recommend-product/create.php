<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SkinRecommendProduct */

$this->title = 'Create Skin Recommend Product';
$this->params['breadcrumbs'][] = ['label' => 'Skin Recommend Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="skin-recommend-product-create">

    <?= $this->render('_form', [
        'model' => $model,
        'skinList' => $skinList,
        'cateList' => $cateList,
    ]) ?>

</div>
