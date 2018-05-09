<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Advertisement;

?>

<div class="advertisement-form">

    <?php $form = ActiveForm::begin(); ?>

    <div>
        <div style="float:left;width:50%;">
            <?= $form->field($model, 'title')->textInput(['maxlength' => false,'style'=>'width:100%']) ?>

            <div style="float:left;margin-right:10%;width:45%;">
                <?= $form->field($model, 'status')->dropDownList(['1'=>'上线','0'=>'下线'],['style'=>'width:100%;']) ?>
            </div>
            <div style="float:left;width:45%;">
                <?= $form->field($model, 'type')->dropDownList(Advertisement::getType(),['style'=>'width:100%;']) ?>
            </div>

            <div style="float:left;margin-right:10%;width:45%;">
                <?= $form->field($model, 'position')->dropDownList(['main'=>'主体','left'=>'右边栏'],['style'=>'width:100%;']) ?>
            </div>
            <div style="float:left;width:45%;">
                <?= $form->field($model, 'sort')->textInput(['value' => $model->sort ? $model->sort : 1,'style'=>'width:100%;']) ?>
            </div>

            <div style="float:left;margin-right:10%;width:45%;">
                <?=$form->field($model, 'start_time')->textInput(['id' => 'start_time', 'value' => $model->start_time?date('Y-m-d',$model->start_time):date('Y-m-d'), 'style'=>'width:100%;']) ?>
            </div>
            <div style="float:left;width:45%;">
                <?= $form->field($model, 'end_time')->textInput(['id' => 'end_time', 'value' => $model->end_time?date('Y-m-d',$model->end_time):date('Y-m-d',time()+3600*24), 'style'=>'width:100%;']) ?>
            </div>

            <?= $form->field($model, 'url')->textInput(['style'=>'width:100%','placeholder'=>'请输入跳转链接']) ?>

        </div>

        <div style="float:left;padding-left:8%;">
            <?= $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
                'config'=>[
                    'domain_url' => Yii::$app->params['uploadsUrl'],
                    'explain' => '推荐尺寸：640x400',
                ],
            ]) ?>
        </div>

    </div>


    <div class="form-group" style="clear:both;">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-success','style' => 'width:100px']) ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>


<?php

$start_time = $model->start_time ? date('Y-m-d',$model->start_time) : date('Y-m-d',time());
$end_time = $model->start_time ? date('Y-m-d',$model->start_time+3600*24) : date('Y-m-d',time()+3600*24);

$script = <<<JS

$('#start_time').datepicker({
    autoclose: true,
    format : 'yyyy-mm-dd',
    'language' : 'zh-CN',
    startDate: "<?= $start_time?>",
});
$('#end_time').datepicker({
    autoclose: true,
    format : 'yyyy-mm-dd',
    'language' : 'zh-CN',
    startDate: "<?= $end_time ?>",
});

JS;
$this->registerJs($script); ?>
