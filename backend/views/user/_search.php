<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Admin;

/* @var $this yii\web\View */
/* @var $model backend\UserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div style="float:left;margin:10px 30px;">
        <?= $form->field($model, 'userType')->dropDownList(['1'=>'用户','2'=>'马甲'],['style'=>'width:120px;','prompt'=>'全部']) ?>
    </div>

    <div style="float:left;margin:10px 30px;">
        <?= $form->field($model, 'referer')->dropDownList(['H5' => 'H5','Android' => 'Android','IOS' => 'IOS'],['style'=>'width:120px;','prompt'=>'全部']) ?>
    </div>

    <div style="float:left;margin:10px 30px;">
        <?= $form->field($model, 'admin_id')->dropDownList(\yii\helpers\ArrayHelper::map(Admin::find()->all(),'id','username'),['style'=>'width:120px;','prompt'=>'全部']) ?>
    </div>

    <div style="float:left;margin:33px 30px">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-success']) ?>
    </div>

    <div style="clear:both;"></div>

    <?php ActiveForm::end(); ?>

</div>
