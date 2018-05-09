<?php
use yii\helpers\Html;
use common\models\ProductCategory;

$this->title = '批量分类上架';
$this->params['breadcrumbs'][] = ['label' => '产品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div><label>批量分类上架</label></div>
<div style="float: left">
<input id="product-keyword" class="form-control" placeholder="请输入关键词">
</div>
<a href="" class="btn btn-success ladda-button" style="margin-left: 5px;display:none" data-method="post" data-pjax="0" target="">确定</a>
<?=Html::dropDownList('','',$cateList,["style" => "height:30px;float:left;margin-left:20px;;margin-right:10px;",'prompt' => '修改分类','class' => 'update-cate']) ?>

<!-- loading -->
<div class="modal fade" id="loading" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop='static'>
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">提示</h4>
      </div>
      <div class="modal-body">
                请稍候...
      </div>
    </div>
  </div>
</div>




<?php 
$script = <<<JS

$(document).on('change','.update-cate',function(){
var cate_id = $(this).val();
var keyword = $('#product-keyword').val().trim();
    
if(keyword == ""){
    alert("请填写关键词");
    $(".update-cate").val("");
    return false;
}
    
$('.update-cate').attr('disabled','disabled');
$('#loading').modal('show');
    
$.ajax({
    url: '/product-details/batch-cate',
    type: 'post',
    dataType: 'json',
    data:{cate_id:cate_id,keyword:keyword},
    success : function(data) {
        if (data.status == '1') {
            art.dialog({content:'修改成功,共执行'+data.num+'条数据',icon:'',ok:function(){},lock: false,opacity:.1,time: 2000});
            window.setTimeout(function(){
                window.location.reload();
            },2000);
        } else {
            alert('操作失败！');
        }
    },
})
});
JS;
$this->registerJs($script);
?>