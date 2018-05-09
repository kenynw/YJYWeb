<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\functions\Tools;
use common\models\Comment;
use common\models\Attachment;

/* @var $this yii\web\View */
/* @var $model common\models\Post */

$this->title = '帖子详情';
$this->params['breadcrumbs'][] = ['label' => '帖子列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

//判断用户类型
if ($model->user) {
    $username = $model->user->username;
    $admin_id = $model->user->admin_id;

    if($admin_id == 0){
        $style = "color:green";
        $title = "用户";
    }else{
        $style = "color:blue";
        $title = "马甲";
    }
}

?>
<div class="post-view">
    <p>
        <?php 
            if ($model->user) {
                echo $model->user->admin_id > 0 ? Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) : '';
            }
        ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'template' => '<tr><th style="width:100px">{label}</th><td>{value}</td></tr>',
        'attributes' => [
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value'     =>  $model->user ? Html::a(Tools::userTextDecode($username), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']) : '',
            ],
            [
                'attribute' => 'topic_id',
                'value'     =>  $model->topic ? $model->topic->title : '',
            ],
            [
                'attribute' => 'content',
                'options'   => ['style' => 'word-break:break-all'],
                'value'     =>  Tools::userTextDecode($model->content),
            ],
            [
                'attribute' => 'img',
                'format' => 'raw',
                'options'   => ['style' => 'word-break:break-all'],
                'value'     =>  $img,
            ],
        ],
    ]) ?>

</div>
<br>
<a name="1F" id="1F" ></a>
<?= $this->render('../comment/index', [
    'data_id' => $model->id,
    'type' => 4,
    'jump_url' => Yii::$app->request->url,
]) ?>

<?php
$script = <<<JS
$(function(){    
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
JS;
$this->registerJs($script);
?>