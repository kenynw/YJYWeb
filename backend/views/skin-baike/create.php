<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SkinBaike */

$this->title = 'Create Skin Baike';
$this->params['breadcrumbs'][] = ['label' => 'Skin Baikes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="skin-baike-create">

    <?= $this->render('_form', [
        'model' => $model,
        'skinList' => $skinList
    ]) ?>

</div>
