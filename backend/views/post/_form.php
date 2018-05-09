<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\models\CommonFun;
use common\models\Attachment;
use backend\assets\AppAsset;
AppAsset::addScript($this, '@web/js/imgUpload.js')

/* @var $this yii\web\View */
/* @var $model common\models\Post */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
        //随机选中马甲
        $shadowList = CommonFun::getShadowList();
        $model->isNewRecord ? $model->user_id = array_rand($shadowList) : false;
        echo $form->field($model, 'user_id')->widget(Select2::classname(), [
            'data' => $shadowList,
            'options' => ['placeholder' => '请选择 ...','class' => 'form-control comment-select2'],
        ])->label("发帖人");
    ?>

    <?php
        //话题列表
        $topicList = CommonFun::getTopicList("status = 1");
        echo $form->field($model, 'topic_id')->widget(Select2::classname(), [
            'data' => $topicList,
            'options' => ['placeholder' => '请选择 ...','class' => 'form-control comment-select2'],
        ])->label("相关话题");
    ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 7]) ?>
    
    <span class="img-icon js-key-show" style="display: none;"><input id="file_upload" multiple="" type="file" name="file_upload"></span>
    <input type="button" value="上传图片" id="uploadbtn" style="" data-imgFilename="post" data-controllerId="post"/><label>（推荐大小：小于1M；推荐尺寸：）</label>

    <div class="add-pic-box">
        <ul class="add-pic-list clearfix" id="file_upload_ul">
            <?php 
            //多张图
            $imgs = $model->isNewRecord ? '' : Attachment::find()->select("attachment")->where("cid = $model->id AND type = 2")->orderBy('aid')->asArray()->column();
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

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
//图片地址
var showUrl = '<?= Yii::$app->params['uploadsUrl']?>';
//删除图片
function removeImg(obj){
    obj.remove();
}
</script>
<?php
$script = <<<JS


JS;
$this->registerJs($script);
?>
