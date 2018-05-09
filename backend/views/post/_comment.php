<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\functions\Tools;
use common\models\Comment;
use common\models\CommentSearch;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CommentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/*只有一级评论特定列*/
$faces = Tools::getFaces();

if ($type && $data_id) {
    //评论列表
    $searchModel = new commentSearch();
    $params = Yii::$app->request->queryParams;
    $params['CommentSearch']['type'] = $type;
    $params['CommentSearch']['post_id'] = $data_id;
    $dataProvider = $searchModel->search($params);
}
?>
<div class="comment-index">
    <div>
        <div class="view-comment-list" style="float:left;font-weight: bold;font-size:18px;height:35px;line-height:35px;margin-right:20px">评论列表</div>
        <?php echo Html::a('<i class="glyphicon glyphicon-plus"></i> 创建评论','javascript:void(0)', ['class' => 'btn btn-success create-comment','data-target'=>'#comment-modal','data-toggle'=>'modal','data-type'=>$type,'data-post-id'=>$data_id,'data-first-id' => '0','data-parent-id' => '0','data-jump-url'=>$jump_url,'data-modal-type' => '0','style'=>'float:left;' ]); ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        "id" => 'grid1',
        "rowOptions" => function ($model, $key, $index, $grid) {
            return $model->status == 1 ? '' : ['style' => 'background-color:#f4f4f4'];
        },
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
                'header' => '#',
                'headerOptions'=> ['width'=> '1%'],
            ],

            [
                'attribute' => 'author',
                'format' => 'raw',
                'options'=>['style'=>'width:10%'],
                'value' => function($model){                    
                    if($model->admin_id == 0){
                        $style = "color:green";
                        $title = "用户";
                        return $model->status == 1 ? Html::a(Tools::userTextDecode($model->author), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-pjax'=>'false','data-toggle'=>'tooltip','data-placement'=>'right']) : Tools::userTextDecode($model->author);
                    }else{
                        $style = "color:blue";
                        $title = "马甲";
                        return $model->status == 1 ? Html::a(Tools::userTextDecode($model->author), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-pjax'=>'false','data-toggle'=>'tooltip','data-placement'=>'right']) : Tools::userTextDecode($model->author);
                    }
                },
                    'filter' => !empty($user_id) ? false : Html::activeDropDownList($searchModel,
                            'user_type', ['0' => '用户', '1' => '马甲'],
                            ['prompt' => '所有']),
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'comment',
                'contentOptions'=>["style"=>"max-width:800px;word-break:break-all;white-space: normal;"],
                'options'=>['style'=>'width:800px'],
                'format' => 'raw',
                'value' => function($model){
                    $faces = Tools::getFaces();
                    //显示评论表情
                    $comment = preg_replace_callback('/(\[[\S\s]+?\])/', function($match) use ($faces){
                        return isset($faces[$match[1]]) ? '<img src='.Yii::$app->params['static_path'].'pc/'.substr($faces[$match[1]],1).' width="22" height="22"/>' : $match[1];
                    }, $model->comment);
                    
                    //一级评论
                    if ($model->first_id == '0' && $model->parent_id == '0') {
                        //显示附件图片
                        $img = empty(Comment::getImg($model->id)) ? '' : '&nbsp;'.Html::a('查看图片','javascript:void(0)',['data-url' => Yii::$app->params['uploadsUrl'],'data-src' => Comment::getImg($model->id),'class' => 'comment-img','data-toggle' => 'modal','data-target' => "#comment-img"]);
                        //二级以上评论数量
                        $count = Comment::getSecondCommentNum($model->id);
                        $countStr = empty($count) ? "" : "<span style='font-size:10px;color:blue;cursor:pointer;' class='reply-list-comment' data-first-id='" . $model->id . "' data-type='0'>共有" . $count . "条回复&nbsp;<i class='fa fa-chevron-down'></i></span>";
                        
                        return !empty($model->comment) ?  Tools::userTextDecode($comment).'&nbsp;&nbsp;'.$img.$countStr: $img.$countStr;
                    } else {
                        $parentComment = Comment::findOne($model->parent_id);
                        
                        $color = $parentComment->admin_id == '0' ? 'green' : 'blue';
                        $str1 = '回复<a href="/user/view?id='.$parentComment->user_id.'" target="_blank" style="color:'.$color.'">@'.Tools::userTextDecode($parentComment->author).'</a>：';
                        
                        $content = Comment::getCommentList($model->id);
//                         $str = Html::a('查看原评论','javascript:void(0)',['style'=>'font-size:10px;color:blue;','data-container'=>'body','data-toggle'=>'popover','data-placement'=>'right','data-html'=>'true','data-content'=>$content,'data-trigger'=>'hover']);
                        $str2 = Html::button('查看原评论',['class'=>'btn btn-default btn-xs','data-container'=>'body','data-toggle'=>'popover','data-placement'=>'right','data-html'=>'true','data-content'=>$content,'data-trigger'=>'focus']);

                        return $str1.Tools::userTextDecode($comment).'&nbsp;&nbsp;'.$str2;
                    }
                },
                'filter' => false,
                'footerOptions' => ['class'=>'hide']
            ],

            [
                'attribute' => 'like_num',
                'contentOptions'=>['class'=>'like_num'],
                'headerOptions' => ['width' => '70px'],
                'footerOptions' => ['class'=>'hide']
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'options'=>['style'=>'width:10%',],
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'footerOptions' => ['class'=>'hide']
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
                        if ($model->status) {
                            if ($model->type != 3) {
                                return Html::a('回复','#',[
                                    'data' => [
                                        'target' => '#comment-modal',
                                        'toggle' => 'modal',
                                        'type' => $model->type,
                                        'first-id' => empty($model->first_id) && empty($model->parent_id) ? $model->id : $model->first_id,
                                        'parent-id' => $model->id,
                                        'post-id' => $model->post_id,
                                        'jump-url'=>$jump_url,
                                        'modal-type' => '0'
                                    ],
                                    'title' => '回复',
                                    'class' => 'btn btn-default btn-xs create-comment'
                                ]);
                            } else {
                                return '';
                            }
                        }
                    },
                    'delete' => function ($url, $model, $key) {
                        $jump_url = Yii::$app->request->url;
                        if ($model->status == 1) {
                            return Html::a('',['comment/delete', 'id' => $model->id,'jump_url'=>$jump_url ,'status'=>'0'], [
                                'data' => [
                                    'confirm' => '确定要删除该评论吗？',
                                    'method' => 'post',
                                ],
                                'class' => 'glyphicon glyphicon-trash self'
                            ]);
                        }else{
                            return Html::a('',['comment/delete',  'id' => $model->id,'jump_url'=>$jump_url,'status'=>'1' ], [
                                'data' => [
                                    'confirm' => '确定要撤回该评论吗？',
                                    'method' => 'post',
                                ],
                                'class' => 'glyphicon glyphicon-repeat self'
                            ]);
                        }
                    },
                    'like' => function ($url, $model, $key) {
                        $csrf = Yii::$app->request->csrfToken;

                        if ($model->status == 1) {
                            return Html::a('','javascript:void(0)', [
                                'data' => ['postId' => $model->post_id, 'commentId' => $model->id, 'type' => $model->type, 'csrf' => $csrf,],
                                'btn-type' => '1',
                                'class' => 'glyphicon glyphicon-heart-empty like'
                            ]);
                        }else{
                            return '&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                    },
                    'update' => function ($url, $model, $key) {                        
                        if ($model->user_type == '1' && $model->first_id == '0' && $model->parent_id == '0') {
                            $jump_url = Yii::$app->request->url;
                            return Html::a('','#',[
                                'data' => [
                                    'target' => '#comment-modal',
                                    'toggle' => 'modal',
                                    'type' => $model->type,
                                    'first-id' => 0,
                                    'parent-id' => 0,
                                    'id' => $model->id,
                                    'post-id' => $model->post_id,
                                    'jump-url'=>$jump_url,
                                    'modal-type' => '1'
                                ],
                                'title' => '回复',
                                'class' => 'glyphicon glyphicon-pencil create-comment'
                            ]);
                        }else{
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
<div class="modal fade img-modal" id="comment-img">
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
//查看原评论
$(function (){
    $("[data-toggle='popover']").popover();
});

//创建评论modal
$(document).on('click', '.create-comment', function () {       
    var id = $(this).attr("data-id");
    var post_id = $(this).attr("data-post-id");
    var type = $(this).attr("data-type");
    var jump_url = $(this).attr("data-jump-url");
    var first_id = $(this).attr("data-first-id");
    var parent_id = $(this).attr("data-parent-id");
    var modal_type = $(this).attr("data-modal-type");

    if(modal_type != '1'){
        var url = '/comment/create';
        $('.comment-modal modal-title').html('创建评论');
    }else{
        var url = '/comment/update?id='+id;
        $('.comment-modal modal-title').html('编辑评论');
    }

    $.get(url, { post_id: post_id , type: type , jump_url: jump_url, first_id: first_id, parent_id: parent_id},
        function (data) {
            $('.comment-modal .modal-body').html(data);
            //用户详情评论人冲突   
            $('.comment-select2').css('display','block');      
        }  
    );
});
//回复完自动下拉
$(function(){
    var data_key_cookie = getCookie('data_key')
    $('tr[data-key="'+data_key_cookie+'"] .reply-list-comment').click();
});
    

//点赞
$(document).on('click', '.like', function () {   
    var postId = this.getAttribute("data-postId");
    var commentId = this.getAttribute("data-commentId");
    var _csrf = this.getAttribute("data-csrf");
    var type = this.getAttribute("data-type");
    var btn_type = this.getAttribute("btn-type");
    
    var box = $(this);
    
    $.ajax({
        url:'/comment/comment-like',
        type:'post',
        dataType:'json',
        data:{postId:postId,commentId:commentId,_csrf:_csrf,type:type},
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


//ajax修改页面状态  
status_ajax("/comment/change-status");

//批量删除
$(document).on('click', '.delete_alls', function () {
    //不同页面的grid id
    var grid_id = $(this).parents("div").attr('id');
    var id = $("#"+grid_id).yiiGridView("getSelectedRows");
    if($(this).attr('disabled') == 'disabled') {
        return false;
    } else {
         if(confirm("确定要删除这些数据吗？")){
         $.ajax({
            url: '/comment/delete-all',
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
    //不同页面的grid id
    var grid_id = $(this).parents("div").attr('id');
    if ($("#"+grid_id+" input[type='checkbox']").is(':checked')) {
        $('#'+grid_id+' .delete_alls').attr('disabled',false);
        $('#'+grid_id+' .delete_alls').addClass('btn-danger');
    } else {
        $('#'+grid_id+' .delete_alls').attr('disabled','disabled');
        $('#'+grid_id+' .delete_alls').removeClass('btn-danger');
    }
})
$("input[name='id_all']").on('click',function(){
    //不同页面的grid id
    var grid_id = $(this).parents("div").attr('id');
    $("#"+grid_id+" tbody input[type='checkbox']").each(function(){
        if($(this).is(':checked')){
            $('#'+grid_id+' .delete_alls').attr('disabled','disabled');
            $('#'+grid_id+' .delete_alls').removeClass('btn-danger');
        } else {
            $('#'+grid_id+' .delete_alls').attr('disabled',false);
            $('#'+grid_id+' .delete_alls').addClass('btn-danger');
            return false;
        }
    })
})

//图片弹窗
$('body').on('click', '.comment-img',function () {
    var url = this.getAttribute("data-url");
    var src = this.getAttribute("data-src");
    $('#comment-img .image').attr("data-index",'0');

    var srcArr = unserialize(''+src+'');
    var srcLeng = srcArr.length;

    $('#comment-img .modal-body img').attr('src', url+srcArr[0]);
    
    $('#comment-img .modal-body img').attr('data-url', url);
    $('#comment-img .modal-body img').attr('data-src', src);
    
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
$('#comment-img .tabbtn').click(function(){  
    var url = $('#comment-img .image').attr("data-url");   
    var srcArr = unserialize(''+$('#comment-img .image').attr("data-src")+'');
    var srcLeng = srcArr.length;
    
    if($(this).hasClass('previous-pic')){
        var index = $('#comment-img .image').attr("data-index");
        index = parseInt(index) - 1;
    }else if($(this).hasClass('next-pic')){
        var index = $('#comment-img .image').attr("data-index");
        index = parseInt(index) + 1;
    }
    
    $('#comment-img .image').attr('data-index',index);
    var src = url+srcArr[index];
    $('#comment-img .image').attr('src', src);

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

//回复列表
$(document).on('click', '.reply-list-comment', function () {
    var first_id = $(this).attr("data-first-id");
    var type = $(this).attr("data-type");
    var _this = $(this);
    var modal_type = $(this).attr("data-modal-type");
    
    if(type == 1){
        $(this).find("i").addClass("fa-chevron-down");
        $(this).find("i").removeClass(" fa-chevron-up");
        
        $(this).parent("td").find("#reply-box").remove();
        $(this).attr("data-type",'0');
        return false;
    }else{
        $(this).find("i").removeClass("fa-chevron-down");
        $(this).find("i").addClass(" fa-chevron-up");
        
        $(this).attr("data-type",'1');
    }
    
    $.ajax({
        url: '/comment/reply-list',
        type: 'post',
        dataType: 'json',
        data:{first_id:first_id},
        success : function(data) {
		    var html = '<div style="background-color: #F2F2F2;width:100%;margin-top:10px;padding:5px 15px 15px 15px;" id="reply-box">';
		    var result = eval(data);
		    var length = result.length;
		    for(var i = 0;i<length;i++) {
		        var color1 =  result[i]['admin_id'] == 0 ? 'green' : 'blue';
                var color2 =  result[i]['parent_admin_id'] == 0 ? 'green' : 'blue';
                var status =  result[i]['status'] == 0 ? '1' : '0';
                var url = window.location.pathname+window.location.search;
                if (result[i]['parent_status'] == 0) {
                    button = '';
                } else if (result[i]['parent_status'] == 1 && result[i]['status'] == 0) {
                    button = '<a style="cursor:pointer;background: #DDDDDD;padding: 2px 6px 2px 6px;color:#AEAEAE;font-size:12px;border:0px" href="/comment/delete?id='+result[i]['id']+'&jump_url='+url+'&status='+status+'" data-method="post" style="color:#AEAEAE">撤回</a>';
                } else {
                    button = '<a href="javascript:void(0);" class="create-comment" data-target="#comment-modal" data-toggle="modal" data-type="'+result[i]['type']+'" data-first-id="'+result[i]['first_id']+'" data-parent-id="'+result[i]['id']+'" data-post-id="'+result[i]['post_id']+'" data-jump-url="'+url+'" data-modal-type="0" style="cursor:pointer;background: #DDDDDD;padding: 2px 6px 2px 6px;color:#AEAEAE;font-size:12px;border:0px">回复</a>&nbsp;<a href="javascript:void(0);" class="like" data-postid="'+result[i]['post_id']+'" data-commentid="'+result[i]['id']+'" data-type="'+result[i]['type']+'" btn-type = "2" style="cursor:pointer;background: #DDDDDD;padding: 2px 6px 2px 6px;color:#AEAEAE;font-size:12px;border:0px">赞</a>&nbsp;<a style="cursor:pointer;background: #DDDDDD;padding: 2px 6px 2px 6px;color:#AEAEAE;font-size:12px;border:0px" href="/comment/delete?id='+result[i]['id']+'&jump_url='+url+'&status='+status+'" data-method="post" data-confirm="确定要删除该评论吗？" style="color:#AEAEAE">删除</a>';
                }

                if (result[i]['first_id'] == result[i]['parent_id']) {
                    //二级回复
                    html += '<div style="width:100%;padding:0 0 5px 0;"><a href="/user/view?id='
                            +result[i]['user_id']+'" target="_blank"><span style="color:'+color1+'">'
                            +result[i]['author']+'</span></a> ：'
                            +result[i]['comment']+'&emsp;<span style="color:#AEAEAE;font-size:12px;"><br>'
                            +result[i]['created_at']
                            +'</span>&nbsp;</div>'+button;
                } else {
                    //二级以上回复
                    html += '<div style="width:100%;padding:0 0 5px 0;"><a href="/user/view?id='
                            +result[i]['user_id']+'" target="_blank"><span style="color:'+color1+';">'
                            +result[i]['author']+'</span></a> 回复<a href="/user/view?id='+result[i]['parent_user_id']+'" target="_blank"><span style="color:'+color2+'">'+result[i]['parent_username']+'</span></a>：'
                            +result[i]['comment']+'&emsp;<span style="color:#AEAEAE;font-size:12px;"><br>'
                            +result[i]['created_at']
                            +'</span>&nbsp;</div>'+button;
                }
		    }
            _this.parent("td").append(html);
        },
        error : function(data) {
            alert('操作失败！');
        }
    });

});
JS;
$this->registerJs($script);
?>