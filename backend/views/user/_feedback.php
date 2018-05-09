<?php

use yii\helpers\Html;

?>
<div style="margin:20px;">
<p>
<?=Html::a('反馈记录','javascript:void(0)', ['class' => 'btn btn-success user-view-feedback','data-target' => '#file6','data-toggle' => 'modal','data-id' => $id]);?>
</p>
</div>    

<!--用户详情回复记录弹框-->
<div class="modal fade" id="file6" style="padding-right: 5px">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">反馈回复记录</h4>
            </div>
            <div class="modal-body" style="min-height:; max-height:1000px;overflow-y:auto;padding:10px">
            </div>
        </div>
    </div>
</div>
<?php 
$script = <<<JS
//详情页回复反馈记录弹窗
$('.user-view-feedback').on('click', function(){
    var id = $(this).attr("data-id");
    $.get('/user-feedback/create', {id: id},
    function (data) {
        $('#file6 .modal-body').html(data);
    });
})
JS;
$this->registerJs($script);
?>






