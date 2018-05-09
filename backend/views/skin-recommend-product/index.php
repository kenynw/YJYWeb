<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Skin;
use common\models\ProductCategory;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SkinRecommendProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '肤质推荐产品列表';
$this->params['breadcrumbs'][] = $this->title;

//肤质
$skinid = Skin::find()->asArray()->all();
$skinList = [];
foreach ($skinid as $key=>$val) {
    $skinList[$val['skin']] = $val['skin'].'('.$val['explain'].')';
}

?>
<div class="skin-recommend-product-index">

    <p>
        <?= Html::a('添加产品','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#product-modal','data-toggle'=>'modal']) ?>
    </p>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showFooter' => true,
        'columns' => [
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                "header"=>'',
                'headerOptions' => ['width' => '30px'],
                'footer' => '<span style="float:left;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::button("批量删除", ["class" => "btn delete_alls",'style' => 'float:left;margin-left:20px;margin-top:-5px','disabled' => 'disabled']),
                'footerOptions' => ['colspan' => 5],
            ],
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '3%']
            ],            
            [
                'label' => '产品ID',
                'attribute' => 'product_id',
                'options' => ['width' => '7%'],
                'value' => function ($model) {
                    return $model->product_id;
                }
            ],
            [
                'attribute' => 'skin_name',
                'options' => ['style'=>'width:23%'],
                'options' => ['style'=>'width:23%;'],
                'contentOptions' => ['style'=>'line-height:25px'],
                'format' => 'raw',
                'value' => function ($model) use ($skinList) {
                    return $model->skin_name."<br>".$model->skin->explain;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'skin_name',$skinList,
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'cate_id',
                'options' => ['style'=>'width:10%'],
                'value' => function ($model) use ($cateList) {
                    return $cateList[$model->cate_id];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'cate_id',$cateList,
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'product_name',
                'options' => ['width' => '20%'],
            ],
            [
                'attribute' => 'price',
                'options' => ['width' => '7%'],
                'value' => function($model) {
                    return empty($model->price) ? '' : '¥'.$model->price;
                }
            ],
            [
                'attribute' => 'form',
                'headerOptions'=> ['width'=> '7%'],
                'value' => function($model) {
                    return empty($model->form) ? '' : $model->form;
                }
            ],
            [
                'attribute' => 'star',
                'format' => 'raw',
                'options' => ['width' => '7%'],
                'value' => function($model){
                    return $model->star.'星';
                },
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'header' => '操作',
            ],
        ],
    ]); ?>
</div>
<!--产品弹框-->
<div class="modal fade product-modal" id="product-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">添加产品</h4>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>
<?php 
$script = <<<JS
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//添加产品
$(document).on('click', '.data-update', function () {    
    var url = '/skin-recommend-product/create';
    $.get(url, { },
        function (data) {
            $('.modal-body').html(data);  
        }  
    );
});

//批量删除
$(document).on('click', '.delete_alls', function () {
    var id = $('#w0').yiiGridView("getSelectedRows");
    if($(this).attr('disabled') == 'disabled') {
        return false;
    } else {
         if(confirm("确定要删除这些数据吗？")){
         $.ajax({
            url: '/skin-recommend-product/delete-all',
            type: 'post',
            dataType: 'json',
            data:{id:id},
            success : function(data) {
                if (data.status == "0") {
                    alert('操作失败');
                }
                if (data.status == "1") {
//                     alert('操作成功！');
                    window.location.reload();
                }
            },
            error : function(data) {}
        });
     }
    }
});
$("input[type='checkbox']").on('click',function(){
    if ($("#w0 input[type='checkbox']").is(':checked')) {
        $('#w0 .delete_alls').attr('disabled',false);
        $('#w0 .delete_alls').addClass('btn-danger');
    } else {
        $('#w0 .delete_alls').attr('disabled','disabled');
        $('#w0 .delete_alls').removeClass('btn-danger');
    }
})
$("input[name='id_all']").on('click',function(){
    $("#w0 tbody input[type='checkbox']").each(function(){
        if($(this).is(':checked')){
            $('#w0 .delete_alls').attr('disabled','disabled');
            $('#w0 .delete_alls').removeClass('btn-danger');
        } else {
            $('#w0 .delete_alls').attr('disabled',false);
            $('#w0 .delete_alls').addClass('btn-danger');
            return false;
        }
    })
})
    
//ajax修改页面状态   
// status_ajax("/product-category/change-status");

JS;
$this->registerJs($script);
?>
