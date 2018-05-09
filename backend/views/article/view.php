<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Article;
use common\models\CommentSearch;
/* @var $this yii\web\View */
/* @var $model common\models\Article */

$this->title = "文章详情";
$this->params['breadcrumbs'][] = ['label' => '文章列表页', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary'])  ?>&nbsp;&nbsp;&nbsp;
    <?php echo Html::a('删除', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => '确定删除这篇文章吗?',
            'method' => 'post',
        ],
    ]) ?>
</p>

<style>
    .box p img{ width:90% !important;}
</style>

<div class="article-view">
    <div class="article-form">
        <div style="height:150px;">
            <div style="height:150px;float:left;margin-right:30px;margin-bottom:30px;">
                <?= Html::img(Yii::$app->params['uploadsUrl'] . $model->article_img,['height'=>"150px;"]); ?>
            </div>

            <div style="width:50%;float:left;font-size:14px;line-height: 30px;">

                <p><b>&emsp;&emsp;&emsp;&emsp;标题</b>：<?= $model->title?></p>
                <p><b>&emsp;&emsp;&emsp;关键词</b>：<?=empty($tagNameArr) ? '无' : join('，',$tagNameArr); ?></p>
                <p><b>&emsp;&emsp;&emsp;阅读量</b>：<?= $model->click_num ?></p>
                <p><b>&emsp;&emsp;针对肤质</b>：<?= empty($model->skin['skin']) ? '' : $model->skin['skin'].'('.$model->skin['explain'].')'; ?></p>
                <p><b>&emsp;&emsp;&emsp;&emsp;状态</b>：<?= $model->status ? '<span class="label label-success">上架</span>' : '<span class="label label-default">下架</span>' ?> &emsp;&emsp;&emsp;
                    <b>&emsp;&emsp;是否推荐</b>：<?= $model->is_recommend ? '<span class="label label-success">推荐</span>' : '<span class="label label-default">默认</span>' ?>&emsp;&emsp;&emsp;
                    <b>&emsp;&emsp;是否置顶</b>：<?= $model->stick ? '<span class="label label-success">置顶</span>' : '<span class="label label-default">默认</span>' ?></p>
                <p><b>文章一级分类</b>：<?= Article::getFirstClass($model->cate_id) ?></p>
                <p><b>文章二级分类</b>：<?= Article::getSecondClass($model->cate_id) ?></p>
                <!-- <p><b>&emsp;&emsp;文章链接</b>：<?//echo  HTML::a(Yii::$app->params['frontendUrl'] ."article/details?id=".$model->id."&type=app",Yii::$app->params['frontendUrl'] ."article/details?id=".$model->id."&type=app",['target'=>'_blank']); ?></p> -->

            </div>
        </div>

        <div style="clear:both;"></div>
        <label class=" control-label"><?= Html::a('文章内容', 'javascript:void(0)', ['class' => 'btn btn-default btn-sm draw','title'=>'点击可以收缩'])  ?></label>
        <div class="form-group box" style="width:92%;border:1px solid black;padding:8px;margin-bottom: 40px;" >
            <?= $model->content; ?>
        </div>
    </div>
</div>

<a name="1F" id="1F" ></a>
    <?= $this->render('../comment/index', [
        'data_id' => $model->id,
        'type' => 2,
        'jump_url' => Yii::$app->request->url
    ]) ?>

<!--右侧导航框-->
<!--<div class="izl-rmenu">-->
<!--    <a href="#" class="sel_product">-->
<!--        <div></div><span>文章详情</span>-->
<!--    </a>-->
<!--    <a href="#1F" class="del_product" >-->
<!--        <div></div><span>评论列表</span>-->
<!--    </a>-->
<!--    <a href="javascript:void(0)" class="btn_top" >-->
<!--        <div></div><span>回到顶部</span>-->
<!--    </a>-->
<!--</div>-->


<?php
$frontendUrl = Yii::$app->params['frontendUrl'];
$articleUrl  = Yii::$app->urlManagerF->createUrl(['product/details','id'=>'']);

$script = <<<JS

var cookie = 'article_draw' + $model->id;

$(function(){
    //隐藏文章详情
    var status=getCookie(cookie);
    if(status == 1 || status == ""){
        $(".box").show();
    }else{
        $(".box").hide();
    }  
    
    //显示评论数
    if($(".comment-index .summary").length > 0){
        var num = $(".comment-index .summary").find("b").eq(1).text();
        var content = "评论列表("+ num +")";
        $(".view-comment-list").html(content);
    }
    
    //定位到#comment
    var urlStr = window.location.href;
    if(!urlStr.match(/t$/) && (urlStr.match(/p/) || urlStr.match(/CommentSearch/))){
        window.location.href = urlStr+'#comment';
    }
});

$(document).on("click",'.draw',function(){
    
    var status = getCookie(cookie);
    if(status == 1 || status == ""){
        $(".box").hide();
        setCookie(cookie,0,300);
    }else{
        $(".box").show();
        setCookie(cookie,1,300);
    }
});

$(".xgsp").on("click",function(){
    var id  = $(this).attr('data-id');
    var url = '$articleUrl' + id;
    $(this).children('a').attr("href",url);
});
JS;
$this->registerJs($script);
?>