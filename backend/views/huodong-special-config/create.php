<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\HuodongSpecialConfig */

$this->title = '创建活动';
$this->params['breadcrumbs'][] = ['label' => '活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="huodong-special-config-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
