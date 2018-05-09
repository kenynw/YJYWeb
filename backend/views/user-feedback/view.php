<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UserFeedback */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => '查看回复记录', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-feedback-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'username',
            'content',
            [
                'label' => '回复记录',
                'format'    => 'raw',
                'value' => $record 
            ],
        ],
    ]) ?>

</div>

<!-- 图片弹窗 -->
<div class="modal fade" id="img">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">图片详情</h4>
            </div>
            <div class="modal-body" style="text-align:center">
                <img src="" class="image" class="kv-preview-data file-preview-image file-zoom-detail" style="width: 50%; height: 50%;">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php 
$script = <<<JS
//图片弹窗
$('.user-feedback-view .img').on('click', function(){
    $('.image').attr('src', this.getAttribute("data-url"));
});

JS;
$this->registerJs($script);
?>