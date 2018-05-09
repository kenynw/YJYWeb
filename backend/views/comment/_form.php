<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\assets\CommentAsset;
CommentAsset::register($this);
?>
<?=Html::cssFile('@web/css/loading/ladda-themeless.min.css')?>

<div class="comment-form">

    <?php $form = ActiveForm::begin(); ?>

    <div style="display:none;">
        <input name="jump_url" value="<?= $_GET['jump_url']; ?>"/>
        <?= $form->field($model, 'first_id')->textInput(['value'=>$_GET['first_id']]) ?>
        <?= $form->field($model, 'parent_id')->textInput(['value'=>$_GET['parent_id']]) ?>
        <?= $form->field($model, 'type')->textInput(['value'=>$_GET['type']]) ?>
        <?= $form->field($model, 'post_id')->textInput(['value'=>$_GET['post_id']]) ?>
        <?= $form->field($model, 'admin_id')->textInput(['value'=>Yii::$app->user->identity->id ]) ?>
        <input type="text" id="comment-data_key" class="form-control" name="Comment[data_key]">
    </div>

    <?php
        //随机选中马甲
        $model->isNewRecord ? $model->user_id = array_rand($shadowList) : false;
        echo $form->field($model, 'user_id')->widget(Select2::classname(), [
            'data' => $shadowList,
            'options' => ['placeholder' => '请选择 ...','class' => 'form-control comment-select2'],
        ])->label("评论人");
    ?>

    <?php 
        if ($_GET['type'] == '1' && $_GET['parent_id'] == '0' && $_GET['first_id'] == '0') {
            echo $form->field($model, 'star')->dropDownList(['1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星'],['prompt'=>'请选择'],['style'=>'width:100px']);
        }
    ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => '6','class' => 'form-control comment-face-icon']) ?>
    
    <?php 
        if ($_GET['parent_id'] == '0' && $_GET['first_id'] == '0') {
            if ($_GET['type'] == '2') {
                echo $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：90x90',
                    ],
                ]);
            } elseif ($_GET['type'] == '1') { ?>
                <span class="img-icon js-key-show" style="display: none;"><input id="file_upload" multiple="" type="file" name="file_upload"></span>
                <input type="button" value="上传图片" id="uploadbtn" style="" data-imgFilename="comment_img" data-controllerId="comment"/>

                <div class="add-pic-box">
                    <ul class="add-pic-list clearfix" id="file_upload_ul">
                        <?php 
                        if (!empty($imgs)) {
                            foreach ($imgs as $key => $pic) {
                                $keyid = 'upload_img_'.$key;
                                echo '<li class="img_file_li" id="'.$keyid.'"><a><input type="text" id="post-views_num" class="form-control" name="img[pic_list][]" value="'.$pic.'" style="display:none;">
                                <img src="'.Yii::$app->params['uploadsUrl'].$pic.'" data="'.Yii::$app->params['uploadsUrl'].$pic.'" style="width:100px;height:100px;" alt="" class="images" id="image'.$key.'" data-key ="'. $key.'">
                                <i class="icon-close" onclick=removeImg($("#'.$keyid.'"))></i></a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
      <?php } 
        } ?>

    <span class="face-icon" id="comment-face-icon" style="display: none"></span>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认创建' : '保存', ['class' => 'btn btn-success','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?=Html::jsFile('@web/js/imgUpload.js')?>
<script>
//图片地址
var showUrl = '<?= Yii::$app->params['uploadsUrl']?>';
</script>
<?php
$script = <<<JS
//防止重复提交
$('form').on('beforeValidate', function (e) {
    $(':submit').attr('disabled', true).addClass('disabled');
});
$('form').on('afterValidate', function (e) {
    if (cheched = $(this).data('yiiActiveForm').validated == false) {
        $(':submit').removeAttr('disabled').removeClass('disabled');
    }
});
$('form').on('beforeSubmit', function (e) {
    $(':submit').attr('disabled', true).addClass('disabled');
    var data_key = $('#comment-first_id').val();
    setCookie('data_key',data_key,10000);
});
    
//清空modal
$('.close').on('click', function(){
    $(".comment-modal .modal-body").empty();
});
$(function(){
    // 绑定表情
    $('.face-icon').SinaEmotion($('.comment-face-icon'));
    var sinaEmotions;
    $('#comment-face-icon').SinaEmotion($('#comment-comment'));
})

//删除图片
function removeImg(obj){
    obj.remove();
}

JS;
$this->registerJs($script);
?>