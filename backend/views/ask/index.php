<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\functions\Tools;
use common\models\AskSearch;
use yii\widgets\Pjax;
use common\models\AskReply;
use common\models\Ask;
use backend\models\CommonFun;

//详情问答
if (!empty($user_id) || !empty($data_id)) {
    empty($user_id) ? false : $search['AskSearch']['user_id'] = $user_id;
    empty($data_id) ? false : $search['AskSearch']['product_id'] = $data_id;

    //搜索
    $search['AskSearch']['type'] = isset($_GET['AskSearch']['type']) ? $_GET['AskSearch']['type'] : "";
    $search['AskSearch']['userType'] = isset($_GET['AskSearch']['userType']) ? $_GET['AskSearch']['userType'] : "";
    $search['AskSearch']['subject'] = isset($_GET['AskSearch']['subject']) ? $_GET['AskSearch']['subject'] : "";
    $search['AskSearch']['product_name'] = isset($_GET['AskSearch']['product_name']) ? $_GET['AskSearch']['product_name'] : "";

    $searchModel = new AskSearch();
    $dataProvider = $searchModel->search($search);
    $replyModel = new AskReply();
    $askModel = new Ask();
    $shadowList = CommonFun::getShadowList();
} else {
    $this->title = '问答管理';
    $this->params['breadcrumbs'][] = $this->title;
}
?>
<div class="ask-index">
    <div>        
        <?php if(!empty($data_id)){
            echo '<div style="float:left;font-weight: bold;font-size:18px;width:100px;height:35px;line-height:35px;">问答列表</div>';
            echo Html::a('<i class="glyphicon glyphicon-plus"></i> 创建问题','javascript:void(0)', ['class' => 'btn btn-success create-ask','data-target'=>'#ask-modal','data-id'=>$data_id,'data-toggle'=>'modal','style'=>'float:left;' ]);
            echo '<div style="clear:both;margin-bottom: 20px;"></div>';
        } ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => 'ask-grid'],
        'id' => 'grid3',
        'showFooter' => true,
        'columns' => [
            [
                "class" => 'yii\grid\CheckboxColumn',
                "name" => "id",
                "header"=>'',
                'options' => ['width' => '2%'],
                'footer' => '<span style="float:left"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>'.Html::button("批量删除", ["class" => "btn delete_alls_ask",'style' => 'float:left;margin-left:20px;margin-top:-5px','disabled' => 'disabled']),
                'footerOptions' => ['colspan' => 5],
                
            ],
            [
                'label' => '问答内容',
                'format'    => 'raw',
                'attribute' => 'subject',
                'options' => ['width' => '20%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:25%'],
                'value'     => function($model){
                    $data = "";
                    if($model->type == 1){
                        $data .= "<label class='label label-success'>问</label>&nbsp;&nbsp;&nbsp;";
                        $data .= Tools::userTextDecode($model->subject) . "&nbsp;&nbsp;&nbsp;";
                        $data .= $model->total ? "<span style='font-size:10px;color:blue;cursor:pointer;' class='reply-list' data-askid='" . $model->id . "' data-type='0'>" . $model->total . "条回答&nbsp;<i class='fa fa-chevron-down'></i></span>" : "";
                    }else{
                        $data .= "<div class='label label-info' style='float:left;margin-right:2%;'>答</div>";
                        $data .=  "<div style='color:#666666;width:60%;float:left;word-break:break-all;overflow:auto;'>" . Tools::userTextDecode($model->reply);
                        $data .= !empty($model->picture) ? '&nbsp;'.Html::a('查看图片','javascript:void(0)',['data-url' => Yii::$app->params['uploadsUrl'].$model->picture,'class' => 'ask-img','data-toggle' => 'modal','data-target' => "#ask-img"])."</div>" : '';
                    }

                    return $data;
                },
            ],
            [
                'label' => '问题',
                'options' => ['width' => '20%'],
                'value' => function ($model) {
                    if ($model->type == 1) {
                        return '';
                    } else {
                        return $model->subject;
                    }
                }
            ],
            [
                'attribute' => 'type',
                'format'    => 'raw',
                'options' => ['width' => '5%'],
                'value'     => function($model){
                    return Html::a('',['javascript:void(0)'], [
                        'data' => [
                            'askid' => $model->id,
                            'type' => $model->type,
                            'replyid' => $model->replyid
                        ],
                        'class'=>'sel-data'
                    ]);
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'type', ['1'=>'提问','2'=>'回答'],
                    ['prompt'=>'所有']
                )
            ],
            [
                'attribute' => 'product_name',
                'label' => '针对产品',
                'format'    => 'raw',
                'options' => ['width' => '15%'],
                'value' => function($model){
                    return Html::a($model->product_name,['product-details/view','id'=>$model->product_id],['data-pjax'=>'false','target'=>'_blank']);
                }
            ],
            [
                'label' => '问答人',
                'format'    => 'raw',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    if($model->type == 1){
                        $user = User::findOne($model->a_uid);
                        $style = $user->admin_id ? "color:blue" : "color:green";
                        $title = $user->admin_id ? "马甲" : "用户";
                        return Html::a(Tools::userTextDecode($model->a_name), ['user/view','id'=> $model->a_uid], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right','data-pjax'=>'false','target'=>'_blank']);
                    }else{
                        $user = User::findOne($model->r_uid);
                        $style = $user->admin_id ? "color:blue" : "color:green";
                        $title = $user->admin_id ? "马甲" : "用户";
                        return Html::a(Tools::userTextDecode($model->r_name), ['user/view','id'=> $model->r_uid], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right','data-pjax'=>'false','target'=>'_blank']);
                    }
                },
                'filter' => !empty($user_id) ? false : Html::activeDropDownList($searchModel,
                    'userType', ['1' => '用户', '2'=> '马甲'],
                    ['prompt'=>'所有']
                ),
            ],
            [
                'attribute' => 'add_time',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    return date('Y-m-d H:i:s', $model->add_time);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}&emsp;{update}&emsp;{like}',
                'header' => '操作',
                'options' => ['width' => '20%'],
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        if($model->type == 1){
                            return Html::a('',['ask/delete', 'id' => $model->id,'url' => Yii::$app->request->url], [
                                'data' => [
                                    'confirm' => '确定要删除该问答吗？',
                                    'method' => 'post',
                                ],
                                'class' => 'glyphicon glyphicon-trash self'
                            ]);
                        }else{
                            return Html::a('',['ask-reply/delete','id' => $model->replyid,'url' => Yii::$app->request->url], [
                                'data' => [
                                    'confirm' => '确定要删除该问答吗？',
                                    'method' => 'post',
                                ],
                                'class' => 'glyphicon glyphicon-trash self'
                            ]);
                        }
                    },
                    'update' => function ($url, $model, $key) {
                        if($model->type == 1){
                            return Html::a('<span class="label label-default">回答</span>', 'javascript:void(0)', [
                                'data-toggle' => 'modal',
                                'data-target' => '#reply-modal',
                                'class' => 'data-update',
                                'data-id' => $model->id,
                            ]);
                        }else{
                            return "";
                        }
                    },
                    'like' => function ($url, $model, $key) {
                        if($model->type == 1){
                            return "";
                        }else{
                            return Html::a('','javascript:void(0)', [
                                'data' => ['askid' => $model->id, 'replyid' => $model->replyid],
                                'class' => 'glyphicon glyphicon-heart-empty like'
                            ]);
                        }
                    },
                ]
            ],
        ],
    ]); ?>

</div>

<!--回答弹框-->
<div class="modal fade bs-example-modal-lg ask-modal" id="reply-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">创建回答</h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'action' => ['ask-reply/create'],
                    'method'=>'post',
                ]); ?>
                    <div style="display:none;">
                        <?= $form->field($replyModel, 'askid')->textInput() ?>
                        <input type="text" id="askreply-url" class="form-control" name="AskReply[url]" value="<?=Yii::$app->request->url ?>">
                    </div>

                    <?php
                    //随机取一个
                    $replyModel->user_id = array_rand($shadowList);
                    echo $form->field($replyModel, 'user_id')->widget(Select2::classname(), [
                        'data' => $shadowList,
                        'options' => ['placeholder' => '请选择 ...'],
                    ])->label("答题人");
                    ?>

                    <?= $form->field($replyModel, 'reply')->textarea(['rows' => '6','class' => 'form-control']) ?>
                    
                    <?php //echo $form->field($replyModel, 'picture')->widget('common\widgets\file_upload\FileUpload',[
//                         'config'=>[
//                             'serverUrl' => '/ask-reply/uploads?action=uploadimage',
//                             'domain_url' => Yii::$app->params['uploadsUrl'],
//                             'explain' => '推荐尺寸：90x90',
//                         ],
//                         ]);
                    ?>

                    <div class="form-group">
                        <?= Html::submitButton('确认创建', ['class' =>'btn btn-success','style' => 'width:100px']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<!--提问弹框-->
<div class="modal fade bs-example-modal-lg" id="ask-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">创建问题</h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'action' => ['ask/create?url='.Yii::$app->request->url],
                    'method'=>'post',
                ]); ?>
                    <div style="display:none;">
                        <?= $form->field($askModel, 'product_id')->textInput() ?>
                    </div>

                    <?php
                    //随机取一个
                    $askModel->user_id = array_rand($shadowList);
                    echo $form->field($askModel, 'user_id')->widget(Select2::classname(), [
                        'data' => $shadowList,
                        'options' => ['placeholder' => '请选择 ...'],
                    ])->label("答题人");
                    ?>

                    <?= $form->field($askModel, 'content')->textarea(['rows' => '6','class' => 'form-control']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('确认创建', ['class' =>'btn btn-success','style' => 'width:100px']) ?>
                    </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!-- 图片弹窗 -->
<div class="modal fade img-modal" id="ask-img">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">图片详情</h4>
            </div>
            <div class="modal-body" style="text-align:center">
                <img src="" class="image" class="kv-preview-data file-preview-image file-zoom-detail" style="width: 100%; height: 100%;">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<?php
$script = <<<JS

//回复列表->删除回复
$(document).on('click', '#del-reply', function () {
    var replyid = $(this).attr("data-replyid");
    var url = '/ask-reply/delete?id=' + replyid;
    
    if(confirm("确定要删除数据吗？")){
        $.post(url, {},function(data){
            window.location.reload();
        }); 
    }
});


//回复列表
$(document).on('click', '.reply-list', function () {
    var askid = $(this).attr("data-askid");
    var type = $(this).attr("data-type");
    var _this = $(this);
    
    if(type == 1){
        $(this).find("i").addClass("fa-chevron-down");
        $(this).find("i").removeClass(" fa-chevron-up");
        
        $(this).parent("td").find("#reply-box").remove();
        $(this).attr("data-type",'0');
        return false;
    }else{
        $.ajax({
            url: '/ask-reply/reply-list',
            type: 'post',
            dataType: 'json',
            data:{askid:askid},
            success : function(data) {
                _this.parent("td").append(data);
                _this.find("i").removeClass("fa-chevron-down");
                _this.find("i").addClass(" fa-chevron-up");
                _this.attr("data-type",'1');
            },
            error : function(data) {
                alert('操作失败！');
            }
        });
    }
});

//回复弹框
$(document).on('click', '.data-update', function () {
    var askid = $(this).attr("data-id");
    $("#reply-modal input[name='AskReply[askid]']").val(askid);
});
    
//提问弹框
$(document).on('click', '.create-ask', function () {
    var product_id = $(this).attr("data-id");
    $("#ask-modal input[name='Ask[product_id]']").val(product_id);
});

//处理选择数据
function check_list(){
    var list = {};
    var i = 0;
    $("#grid3 input[name='id[]']").each(function(){
        if($(this).is(':checked')){
            list[i] = {};
            list[i].askid = $(this).parents("tr").find(".sel-data").attr("data-askid");
            list[i].type = $(this).parents("tr").find(".sel-data").attr("data-type");
            list[i].replyid = $(this).parents("tr").find(".sel-data").attr("data-replyid");
            i++;
        }
    });
    
    if(list[0] == undefined){
        alert("请选择商品");
        return false;
    }
    
    console.log(list);
    return list;
}

//批量删除
$(document).on('click', '.delete_alls_ask', function () { 
    var list = check_list();
    $.ajax({
        url: '/ask/delete-all',
        type: 'post',
        dataType: 'json',
        data:{list:list},
        success : function(data) {
            window.location.reload();
        },
        error : function(data) {
            alert('操作失败！');
        }
    });
    
});
$("#grid3 input[type='checkbox']").on('click',function(){
    if ($("#grid3 input[type='checkbox']").is(':checked')) {
        $('#grid3 .delete_alls_ask').attr('disabled',false);
        $('#grid3 .delete_alls_ask').addClass('btn-danger');
    } else {
        $('#grid3 .delete_alls_ask').attr('disabled','disabled');
        $('#grid3 .delete_alls_ask').removeClass('btn-danger');
    }
})
$("#grid3 input[name='id_all']").on('click',function(){
    $("#grid3 tbody input[type='checkbox']").each(function(){
        if($(this).is(':checked')){
            $('#grid3 .delete_alls_ask').attr('disabled','disabled');
            $('#grid3 .delete_alls_ask').removeClass('btn-danger');
        } else {
            $('#grid3 .delete_alls_ask').attr('disabled',false);
            $('#grid3 .delete_alls_ask').addClass('btn-danger');
            return false;
        }
    })
})
    
//图片弹窗
$('body').on('click','.ask-img', function(){
    $('.image').attr('src', this.getAttribute("data-url"));
});
    
//点赞
$(document).on('click', '.ask-grid .like', function () {   
    var askId = this.getAttribute("data-askid");
    var replyId = this.getAttribute("data-replyid");
    
//     var box = $(this);
    
    $.ajax({
        url:'/ask/comment-like',
        type:'post',
        dataType:'json',
        data:{askId:askId,replyId:replyId},
        success:function(data) {
            if (data.status == "0") {
                var d = '马甲：'+data.username+'('+data.userId+')已对该回答点赞！';
                art.dialog({content:d,icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
                
                //更新点赞数
//              var num = parseInt(box.parents("tr").find(".like_num").html());
//              box.parents("tr").find(".like_num").html(num + 1);
            }
            if (data.status == "1") {
                var d = '所有马甲都已点赞！';
                art.dialog({content:d,icon:'',ok:function(){},lock: false,opacity:.1,time: 1});
            }
        },
        error:function(data) {}
   });
});

JS;
$this->registerJs($script);
?>

