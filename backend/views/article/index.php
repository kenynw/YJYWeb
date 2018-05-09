<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\ArticleCategory;
use common\models\Article;

$this->title = '文章列表页';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-index">

    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 创建文章', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        "rowOptions" => function ($model, $key, $index, $grid) {
            return $model->stick == 0 ? '' : ['style' => 'background-color:#eff5fb;'];
        },
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
                'attribute' => 'title',
                'options' => ['width' => '20%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:24%'],
                'format' => 'raw',
                'value' => function($model){
                    return Html::a($model->title, ['article/view','id'=> $model->id]);
                }
            ],
            [
                'attribute' => 'cate_id',
                'header' => '文章一级分类',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    return Article::getFirstClass($model->cate_id);
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'cate_ids',yii\helpers\ArrayHelper::map(ArticleCategory::find()->where(['parent_id'=>0])->all(), 'id', 'cate_name'),
                    ['style'=>'width:100px;','prompt'=>'--- 请选择 ---']
                )
            ],
            [
                'attribute' => 'cate_id',
                'header' => '文章二级分类',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    return Article::getSecondClass($model->cate_id);
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'cate_id','',
                    ['style'=>'width:100px;','prompt'=>'--- 请选择 ---']
                )
            ],
            [
                'attribute' => 'click_num',
                'options' => ['width' => '6.5%'],
            ],
            [
                'attribute' => 'is_recommend',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_recommend == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='is_recommend' data-id='".$model->id."'>已推荐</button>";
                    }else{
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='is_recommend' data-id='".$model->id."'>默认</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_recommend',['0' => '默认','1' => '已推荐'],
                    ['prompt' => '所有'])
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
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'admin_id',
                'value' => function ($model) {
                    return empty($model->admin->username) ? '' : $model->admin->username;
                }
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'options' => ['width' => '15%'],
                'filter' => false,
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'options' => ['width' => '6%'],
                'template' => '{update} {delete} {no_stick}',
                'header' => '操作',
                'buttons' => [
                    'no_stick' => function ($url, $model, $key) {
                        $return = $model->stick > 0 ? Html::a('<span class="glyphicon glyphicon-arrow-down" style="color:#cc0c0c"></span>', "/article/no-stick?id=$model->id", ['title' => '取消置顶','class' => 'self']) : '';
                        return $return;
                    },
                ]
            ],
        ],

        'pager' => [
            'pages' => isset($_GET['pages']) ? $_GET['pages'] : $dataProvider->pagination->defaultPageSize,
        ],

    ]); ?>
</div>


<?php

$cate = isset($_GET['ArticleSearch']['cate_id']) ? $_GET['ArticleSearch']['cate_id'] : "";

$script = <<<JS

$(document).on("click",'#articlesearch-cate_ids',function(){
    $("#articlesearch-cate_id").val("");
});

//获取2级分类
$(function(){
    var cate_id = $("#articlesearch-cate_ids").val();
    var cate = "$cate";
    if(cate_id != ''){
        $.get('/article-category/get-cate-list',{cate_id: cate_id},function(data){
            $("#articlesearch-cate_id").html(data);
           
            if(cate){
                $("#articlesearch-cate_id option").each(function(i){
                    if($(this).attr("value") == cate){
                        $("#articlesearch-cate_id").val(cate);
                    }
                });
                
                //$("#articlesearch-cate_id").val(cate);
            }
        });
    }
});

//ajax修改页面状态  
status_ajax("/article/change-status");

JS;
$this->registerJs($script);
?>
