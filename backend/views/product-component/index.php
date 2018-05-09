<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductComponentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '成分列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-component-index">

    <p>
        <?php echo Html::a('<i class="glyphicon glyphicon-plus"></i> 添加成分','javascript:void(0)', ['class' => 'btn btn-success updates','data-target'=>'#comment','data-toggle'=>'modal',"data-id"=>""]) ?>
    </p>

    <?php
    use yii\bootstrap\Modal;
    //创建修改modal
    Modal::begin([
        'id' => 'comment',
        'header' => '<h4 class="modal-title">成分详情</h4>',
        'size' => "modal-lg",
        'clientEvents' => ['hide.bs.modal'=> 'function(){
            window.location.reload();
        }'],
    ]);
    Modal::end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '5%'],
            ],

            [
                'attribute' => 'id',
                'options' => ['width' => '5%'],
            ],

            [
                'attribute' => 'name',
                'format' => 'raw',
                'options' => ['style' => 'width:19%'],
                'value' => function($model){
                    return Html::a($model->name,'javascript:void(0)', ['class' => 'updates','data-target'=>'#comment','data-toggle'=>'modal',"data-id"=>$model->id,"data-type"=>'view']);
                    //return Html::a($model->name, ['product-component/view','id'=> $model->id]);
                }
            ],

            [
                'attribute'=>'risk_grade',
                'options'=>['style'=>'width:8%'],
                'format' => 'raw',
                'filter' => Html::activeDropDownList($searchModel,
                    'risk_grade', ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9'],
                    ['prompt' => '所有']
                )
            ],

            [
                'attribute'=>'is_active',
                'options'=>['style'=>'width:8%;'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_active == 1){
                        return " <span class='label label-success'>有</span>" ;
                    }else{
                        return " <span class='label label-default'>无</span>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_active', ['0' => '无', '1' => '有'],
                    ['prompt' => '所有']
                )
            ],

            [
                'attribute'=>'is_pox',
                'options'=>['style'=>'width:5%;'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_pox == 1){
                        return " <span class='label label-success'>是</span>" ;
                    }else{
                        return " <span class='label label-default'>否</span>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_pox', ['0' => '否', '1' => '是'],
                    ['prompt' => '所有']
                )
            ],

            [
                'attribute'=>'component_action',
                'options'=>['style'=>'width:19%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:19%']
            ],
            [
                'attribute' => 'product_num',
                'options'=>['style'=>'width:7%'],
                'format' => 'raw',
                'value' => function ($model) {
                    $product_num = empty($model->product_num) ? '0' : $model->product_num;
                    return !empty($model->product_num) ? Html::a($product_num, ['/product-details/index','ProductDetailsSearch[component_id]'=> $model->id]) : 0;
                }
            ],
            [
                'attribute' => 'created_at',
                'options'=>['style'=>'width:15%;'],
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => false,
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'options'=>['style'=>'width:100px'],
                'template' => '{update}&nbsp;&nbsp;{delete}',
                'header' => '操作',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('','javascript:void(0)',[
                                'data' => [
                                    'target' => '#comment',
                                    'toggle' => 'modal',
                                    'id' => $model->id
                                ],
                                'class' => 'glyphicon glyphicon-pencil updates'
                            ]);
                    },


                ]
            ],
        ],
    ]); ?>
</div>

<?php
$script = <<<JS

$(document).on("click",'.updates',function(){
    var id = $(this).attr("data-id");
    var type = $(this).attr("data-type");
    
    if(id){
        var url = '/product-component/update';
    }else{
        var url = '/product-component/create';
    }
    
    $.get(url, { id: id },
        function (data) {
            $('.modal-body').html(data);
            
            if(type == "view"){
                $(".product-component-form").find("input").attr("disabled",true);
                $(".product-component-form").find("select").attr("disabled",true);
                $(".product-component-form").find("textarea").attr("disabled",true);
                $(".product-component-form").find("button").css("display",'none');
                $(".plus-tag-add").css("display",'none');
            }else{
                $(".product-component-form").find("input").attr("disabled",false);
                $(".product-component-form").find("select").attr("disabled",false);
                $(".product-component-form").find("textarea").attr("disabled",false);
                $(".product-component-form").find("button").css("display",'block');  
                $(".plus-tag-add").css("display",'block');
            }
        }  
    );
    
});

JS;
$this->registerJs($script);
?>




















