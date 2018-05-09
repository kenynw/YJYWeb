<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SkinType */

$this->title = '创建';
$this->params['breadcrumbs'][] = ['label' => 'Skin Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="skin-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
