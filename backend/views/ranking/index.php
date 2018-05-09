<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\ProductCategory;
use common\models\Ranking;

/* @var $this yii\web\View */
/* @var $searchModel common\models\RankingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '分类排行榜设置';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="ranking-index">

    <p>
        <?= Html::a('添加排行榜', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showFooter' => true,
        'id' => 'grid-ranking',
        'columns' => [
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                "header"=>'',
                'headerOptions'=> ['width'=> '4%'],
                'footer' => '<span style="float:left;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::dropDownList('','',['1'=>'上架','0'=>'下架'],["style" => "height:30px;float:left;margin-left:10px;",'class' => 'bottom-update-select','data-type' => 'status','prompt' => '状态设置']),
                'footerOptions' => ['colspan' => 5],
            ],
            [
                'attribute' => 'title',
                'headerOptions'=> ['width'=> '30%'],
                'footerOptions' => ['class'=>'hide']
            ],
//             [
//                 'attribute' => 'category_id',
//                 'format' => 'raw',
//                 'value'     => function($model) use($cateList){
//                     return $cateList[$model->category_id];
//                 },
//                 'options' => ['width' => '10%'],
//                 'filter' => Html::activeDropDownList($searchModel,
//                     'category_id',$cateList,
//                     ['prompt' => '所有'])
//             ],
            [
                'attribute' => 'banner',
                'headerOptions'=> ['width'=> '15%'],
                'format'    => 'raw',
                'value'     => function($model){
                    return Html::img(Yii::$app->params['uploadsUrl'] . $model->banner,['height' => '50px']);
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'label' => '产品数',
                'headerOptions'=> ['width'=> '10%'],
                'format'    => 'raw',
                'value' => function ($model) {
                    $product_num = empty(Ranking::getProductNum($model->id)) ? '0' : Ranking::getProductNum($model->id);
                    $title = '<p align="left">'.Ranking::getProductStr($model->id).'</p>';
                    return empty($product_num) ? 0 : Html::a($product_num, 'javascript:void(0)',['title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right','data-html'=>'true']);
                },
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'status',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->status == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='status' data-id='".$model->id."'>已上架</button>";
                    }else{
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='status' data-id='".$model->id."'>已下架</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'status',['0' => '已下架','1' => '已上架'],
                    ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} &nbsp;&nbsp; {delete}',
                'header' => '操作',
            ],
        ],
    ]); ?>
</div>

<?php 
$script = <<<JS
$(function(){
//ajax修改页面状态  
status_ajax("/ranking/change-status");
    
//批量修改
function bottomUpdate(id,type,type_id) {
    var url = window.location.href;
    $.ajax({
    url: '/ranking/bottom-update',
    type: 'post',
    dataType: 'json',
    data:{id:id,type:type,type_id:type_id},
    success : function(data) {
        if (data.status == '1') {
            art.dialog({content:'修改成功',icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            window.location.href = url;
        } else {
            alert('操作失败！');
        }
    },
})
}
$(document).on('change','.bottom-update-select',function(){
var id = $("#grid-ranking").yiiGridView("getSelectedRows");
var type = $(this).attr('data-type');
var type_id = $(this).val();
if(id == ""){
    $(this).val('');
    alert("请选择相应榜单");
    return false;
}
bottomUpdate(id,type,type_id);
});
})
JS;
$this->registerJs($script);
?>