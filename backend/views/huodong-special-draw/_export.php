<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\HuodongSpecialDraw;

/* @var $this yii\web\View */
/* @var $model common\models\BrandSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="brand-search" style="float:right">

    <?php $form = ActiveForm::begin([
        'action' => ['export'],
        'method' => 'post',
        'id' => 'prevent-disabled'
    ]); ?>

    <div class="form-group">
        <?=Html::dropDownList('hdid','',HuodongSpecialDraw::getHuodongNameArr(),['prompt'=>'-- 请选择 --']) ?>
        <?= Html::submitButton('导出', ['class' => 'btn btn-primary btn-xs']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS
    $(".form-group button").click(function(){
        var huodong = $("select[name='hdid']").val();
        if(huodong == ""){
            alert("请选择活动名称");
            return false;
        }
    });
JS;
$this->registerJs($script);
?>
