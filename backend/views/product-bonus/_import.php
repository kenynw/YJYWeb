<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="product-bonus-search" style="float:right">

    <?php $form = ActiveForm::begin([
        'action' => ['import'],
        'method' => 'post',
        'options'=>['enctype'=>'multipart/form-data'],
    ]); ?>

    <div class="form-group">
        <div style="float:left;margin-right:20px;"><a href="<?php echo Yii::$app->params['backendUrl'] . "static/商城商品导入模板.xlsx" ?>" >下载模板</a></div>
        <div style="float:left;width:200px;overflow: hidden;"><input name="file" type="file"/></div>
        <?= Html::submitButton('导入', ['class' => 'btn btn-primary btn-xs']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS
    $(".form-group button").click(function(){
        var file = $("input[name='file']").val();
        if(file == ""){
            alert("请选上传文件");
            return false;
        }
    });
JS;
$this->registerJs($script);
