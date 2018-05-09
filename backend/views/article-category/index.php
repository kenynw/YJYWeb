<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\ArticleCategory;

//搜索后展示时间
$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');

$parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : "";
if($parent_id){
    $cate_name = ArticleCategory::find()->select("cate_name")->andWhere(['id' => $parent_id])->asArray()->one();
}

$this->title = $parent_id ? '文章二级分类页 ------ '.$cate_name['cate_name'] : '文章一级分类页';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="product-category-index">
    <p>
        <?php
            if($parent_id){
                //echo HTML::a("返回",'/article-category/index',['class'=>'btn btn-info'])."&nbsp;&nbsp;&nbsp;&nbsp;";
                echo Html::a('<i class="glyphicon glyphicon-plus"></i> 添加二级分类','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'','data-parent_id'=>$parent_id]);
            }else{
                echo Html::a('<i class="glyphicon glyphicon-plus"></i> 添加一级分类','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'','data-parent_id'=>'0']);
            }
        ?>
    </p>

    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>


    <?php
    use yii\bootstrap\Modal;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
    ]);
    Modal::end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'cate_name',
                'header' => $parent_id ? '二级分类名' : '一级分类名',
                'options' => ['width' => '20%'],
            ],
            [
                'header' => '二级分类名',
                'options' => ['width' => '7%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:25%'],
                'format' => 'raw',
                //判断是否显示
                'visible' => $parent_id == 0 ? '1' : '0',
                'value' => function($model) {
                    return Html::a($model->cate_num, ['article-category/index','parent_id'=> $model->id]);
                }
            ],

            [
                'options' => ['width' => '8%'],
                'header' => '文章数',
                'format' => 'raw',
                'value' => function($model) {
                    $article_num = ArticleCategory::getArticleNum($model->id,$model->parent_id);
                    if($model->parent_id == 0){
                        return !empty($article_num) ? Html::a($article_num, ['article/index','ArticleSearch[cate_ids]'=> $model->id]) : 0;
                    }else{
                        return !empty($article_num) ? Html::a($article_num, ['article/index','ArticleSearch[cate_ids]'=> $model->parent_id,'ArticleSearch[cate_id]'=> $model->id]) : 0;
                    }
                }
            ],
            
            [
                'attribute' => 'order',
                'options' => ['width' => '8%'],
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
            'describe',
            [
                'attribute' => 'created_at',
                'options' => ['width' => '20%'],
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;'])
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
                'header' => '操作',
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
                            'data-toggle' => 'modal',
                            'data-parent_id' => $model->parent_id,
                            'data-target' => '#update-modal',
                            'class' => 'data-update',
                            'data-id' => $key,
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        if ($model->parent_id == '0') {
                            return '';
                        } else {
                            $options = [
                                'title' => Yii::t('yii', '删除'),
                                'data-method' => 'post',        
                                'data-confirm' => '您确定要删除此项吗？删除后该分类下的文章将会归到其一级分类下',
                                'data-pjax' => '0'
                            ];
                        return Html::a('<span class="glyphicon glyphicon-trash" target=""></span>',['/article-category/delete','id' => $model->id],$options);          
                        }
                    },
                ]
            ],
        ],
    ]); ?>
</div>

<?php
$script = <<<JS
$(function(){
//时间搜索框
    $('#start_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    });
    $('#end_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    }); 
});
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    var parent_id = $(this).attr("data-parent_id");
    
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/article-category/create';
    } else {
        $('.modal-title').html("更新");
        var url = '/article-category/update';
    }
    $.get(url, { id: id ,parent_id: parent_id},
        function (data) {
            $('.modal-body').html(data);
        }  
    );
});

//ajax修改页面状态   
status_ajax("/article-category/change-status");
JS;
$this->registerJs($script);
?>
