<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Comment;
use common\functions\Tools;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '帖子列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">
    <p>
        <?= Html::a('发帖子', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [

            [
                'attribute' => 'id',
                'options' => ['width' => '2%'],
            ],
            [
                'attribute' => 'user_type',
                'options' => ['width' => '10%'],
                'format' => 'raw',
                'value' => function($model){                    
                    if ($model->user) {
                        $username = $model->user->username;
                        $admin_id = $model->user->admin_id;
                        
                        if($admin_id == 0){
                            $style = "color:green";
                            $title = "用户";
                            return Html::a(Tools::userTextDecode($username), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']);
                        }else{
                            $style = "color:blue";
                            $title = "马甲";
                            return Html::a(Tools::userTextDecode($username), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']);
                        }
                    } else {
                        return '';
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'user_type', ['1' => '用户', '2' => '马甲'],
                    ['prompt' => '所有']
                ),
            ],
            [
                'attribute' => 'content',
                'options' => ['width' => '30%'],
                'format' => 'raw',
                'contentOptions' => ['style' => 'word-break:break-all;max-width:100%;overflow:hidden;text-overflow:ellipsis;-webkit-line-clamp:5;-webkit-box-orient: vertical;display:-webkit-box;padding-bottom:5px'],
                'value' => function ($model) {
                    return Html::a(Tools::userTextDecode($model->content),['post/view','id'=>$model->id]);
                }
            ],
            [
                'attribute' => 'topic_title',
                'options' => ['width' => '20%'],
                'value' => function ($model) {
                    return empty($model->topic->title) ? '' : $model->topic->title;
                }
            ],   
            [
                'attribute' => 'img',
                'format' => 'raw',
                'options' => ['width' => '5%'],
                'value' => function ($model) {
                    return empty(Comment::getImg($model->id,'2')) ? '-' : Html::a('查看图片','javascript:void(0)',['data-url' => Yii::$app->params['uploadsUrl'],'data-src' => Comment::getImg($model->id,'2'),'class' => 'post-img','data-toggle' => 'modal','data-target' => "#post-img"]);
                } 
            ],
            [
                'attribute' => 'comment_num',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'like_num',
                'contentOptions'=>['class'=>'like_num'],
                'options' => ['width' => '5%'],
            ],    
            [
                'attribute' => 'created_at',
                'options' => ['width' => '10%'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{create}&nbsp;&nbsp;{like}&nbsp;&nbsp;{update}&nbsp;&nbsp;{delete}',
                'header' => '操作',
                'options'=>['style'=>'width:200px'],
                'footerOptions' => ['class'=>'hide'],
                'buttons' => [
                    'create' => function ($url, $model, $key) {
                        $jump_url = Yii::$app->request->url;
                        return Html::a('评论','#',[
                            'data' => [
                                'target' => '#comment-modal',
                                'toggle' => 'modal',
                                'type' => '4',
                                'first-id' => '0',
                                'parent-id' => '0',
                                'post-id' => $model->id,
                                'jump-url'=>$jump_url,
                            ],
                            'title' => '评论',
                            'class' => 'btn btn-default btn-xs create-comment'
                        ]);
                    },
                    'like' => function ($url, $model, $key) {
                        $csrf = Yii::$app->request->csrfToken;
                        return Html::a('','javascript:void(0)', [
                            'data' => ['postId' => $model->id, 'topicId' => $model->topic_id, 'csrf' => $csrf,],
                            'btn-type' => '1',
                            'class' => 'glyphicon glyphicon-heart-empty like'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        if ($model->user) {
                            return $model->user->admin_id > 0 ? Html::a('<span class="glyphicon glyphicon-pencil"></span>',"/post/update?id=$model->id") : '';
                        } else {
                            return '';
                        }               
                    },
                ]
            ],
        ],
    ]); ?>
</div>

<!--评论弹框-->
<div class="modal fade bs-example-modal-lg comment-modal" id="comment-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">创建评论</h4>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<!-- 图片弹窗 -->
<div class="modal fade img-modal" id="post-img">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">图片详情<span class="picnum"></span></h4>
            </div>
            <div class="modal-body" style="text-align:center">
                <img src="" class="image" class="kv-preview-data file-preview-image file-zoom-detail" style="width: 100%; height: 100%;" data-index="0">
            </div>
            <div class="modal-footer">
                <button type='button' class='btn btn-primary tabbtn previous-pic hidden'>
                                    上一张
                </button>
                <button type="button" class="btn btn-primary tabbtn next-pic">
                                    下一张
                </button>
                <label class="page-index" hidden>0</label>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php
$script = <<<JS
//创建评论modal
$(document).on('click', '.create-comment', function () {       
    var id = $(this).attr("data-id");
    var post_id = $(this).attr("data-post-id");
    var type = $(this).attr("data-type");
    var jump_url = $(this).attr("data-jump-url");
    var first_id = $(this).attr("data-first-id");
    var parent_id = $(this).attr("data-parent-id");

    var url = '/comment/create';
    $('.comment-modal modal-title').html('创建评论');

    $.get(url, { post_id: post_id , type: type , jump_url: jump_url, first_id: first_id, parent_id: parent_id},
        function (data) {
            $('.comment-modal .modal-body').html(data);   
        }  
    );
}); 

//点赞
$(document).on('click', '.like', function () {   
    var postId = this.getAttribute("data-postId");
    var topicId = this.getAttribute("data-topicId");
    var _csrf = this.getAttribute("data-csrf");
    var btn_type = this.getAttribute("btn-type");
    
    var box = $(this);
    
    $.ajax({
        url:'/post/comment-like',
        type:'post',
        dataType:'json',
        data:{postId:postId,topicId:topicId,_csrf:_csrf},
        success:function(data) {
            if (data.status == "0") {
                var d = '马甲：'+data.username+'('+data.userId+')已对该评论点赞！';
                art.dialog({content:d,icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
                
                if (btn_type == '1') {
                    //更新点赞数
                    var num = parseInt(box.parents("tr").find(".like_num").html());
                    box.parents("tr").find(".like_num").html(num + 1);
                }
            }
            if (data.status == "1") {
                var d = '所有马甲都已点赞！';
                art.dialog({content:d,icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            }
        },
        error:function(data) {}
   });
});

//图片弹窗
$('body').on('click', '.post-img',function () {
    var url = this.getAttribute("data-url");
    var src = this.getAttribute("data-src");
    $('#post-img .image').attr("data-index",'0');

    var srcArr = unserialize(''+src+'');
    var srcLeng = srcArr.length;

    $('#post-img .modal-body img').attr('src', url+srcArr[0]);
    
    $('#post-img .modal-body img').attr('data-url', url);
    $('#post-img .modal-body img').attr('data-src', src);
    
    if(srcLeng == 1){
        $('.next-pic').addClass('hidden');
        $('.previous-pic').removeClass('hidden');
        $('.picnum').html('(1/1)');
    } else {
        $('.previous-pic').addClass('hidden');
        $('.next-pic').removeClass('hidden');
        $('.picnum').html('(1/'+srcLeng+')');
    }
});
//图片切换
$('#post-img .tabbtn').click(function(){  
    var url = $('#post-img .image').attr("data-url");   
    var srcArr = unserialize(''+$('#post-img .image').attr("data-src")+'');
    var srcLeng = srcArr.length;
    
    if($(this).hasClass('previous-pic')){
        var index = $('#post-img .image').attr("data-index");
        index = parseInt(index) - 1;
    }else if($(this).hasClass('next-pic')){
        var index = $('#post-img .image').attr("data-index");
        index = parseInt(index) + 1;
    }
    
    $('#post-img .image').attr('data-index',index);
    var src = url+srcArr[index];
    $('#post-img .image').attr('src', src);

    if($(this).hasClass('previous-pic')){
        $('.next-pic').removeClass('hidden');    
        if(srcLeng == 0){
            $(this).addClass('hidden');
        }
        if(index == 0){
            $('.previous-pic').addClass('hidden');    
            $('.next-pic').removeClass('hidden');
        }
    }else if($(this).hasClass('next-pic')){
        $('.previous-pic').removeClass('hidden');    
        if(srcLeng == index+1){
            $(this).addClass('hidden');
        }
    }
    
    $('.picnum').html('('+eval(index+1)+'/'+srcLeng+')');
})   

JS;
$this->registerJs($script);
?>