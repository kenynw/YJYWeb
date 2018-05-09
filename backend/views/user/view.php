<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\User;
use common\functions\Tools;
use yii\helpers\Url;
use kartik\tabs\TabsX;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\base\Object;
use common\models\UserFeedback;

$this->title = '用户详情页';
$this->params['breadcrumbs'][] = ['label' => '用户列表', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
$csrf = Yii::$app->request->csrfToken;
?>
<style>
    .table td{border-right:1px solid #ECF0F5;}
</style>

<!-- 用户详情列表 -->

<?php if($model->admin_id){ ?>
<div style="margin-bottom:60px;">
    <table class="table" style="border:1px solid #ECF0F5">
        <tr style="font-size:18px;height:50px;">
            <th colspan="2">基本信息</th>
            <th colspan="2">主要信息</th>
            <th colspan="2">其他信息</th>
        <tr>
            <th rowspan="3" width="3%">头像</th>
            <td rowspan="3" width="12%"><img src="<?=Yii::$app->params['uploadsUrl'].$model->img ?>" width="100" height="100" alt="" data-toggle="modal" data-target="#file"></td>
            <th width="10%">马甲ID</th><td width="20%"><?=$model->id ?></td>
            <th width="10%">所属账号</th><td><?= $userinfo['account'] ?></td>
        </tr>
        <tr>
            <th>马甲名</th><td><span><?= Tools::userTextDecode($model->username) ?></span>&nbsp;<span><?= HTML::a("修改",'javascript:void(0)',['id'=>'usernames']) ?></span><span class="nameerror" style="color:red"></span></td>
            <th>肤质</th>
            <td>
                <?= Html::dropDownList('skin_id',$userinfo['skin_id'],$skinList,['id'=>'skin_id','prompt'=>'-- 请选择 --']) ?>
            </td>
        </tr>
        <tr>
            <th>产品点评数</th><td><?= $userinfo['product_comment_num'] ?></td>
            <th>年龄</th><td>
                <?php
                    //$time = (!empty($model->birth_year) && !empty($model->birth_month) && !empty($model->birth_day)) ? $model->birth_year.'-'.$model->birth_month.'-'.$model->birth_day : '';
                    $time = date('Y') - $model->birth_year;
                    echo Html::input('text', 'created_at', $time, ['id' => 'start_time'])
                ?>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?= $model->admin_id != 0 ? "<button class='btn btn-success btn-xs' data-toggle='modal' data-target='#file3'>更换头像</button>" : "" ?></button>
            </td>
            <th>文章评论数</th><td><?= $userinfo['article_comment_num'] ?></td>
            <th>注册时间</th><td><?=date('Y-m-d H:i:s',$model->created_at) ?></td>
        </tr>
    </table>
</div>

<?php }else{ ?>

    <div style="margin-bottom:60px;">
        <table class="table" style="border:1px solid #ECF0F5">
            <tr style="font-size:18px;height:50px;">
                <th colspan="2">基本信息</th>
                <th colspan="2">主要信息</th>
                <th colspan="2">其他信息</th>
            <tr>
                <th rowspan="3" width="5%">头像</th>
                <td rowspan="3" width="18%"><img src="<?=Yii::$app->params['uploadsUrl'].$model->img ?>" width="100" height="100" alt="" data-toggle="modal" data-target="#file"></td>
                <th width="10%">来源</th><td width="20%"><?=$model->referer ?></td>
                <th width="10%">颜值分</th>
                <td>
                    <?= $model->rank_points ? Html::a($model->rank_points,'javascript:void(0)', ['data-target'=>'#file2','data-toggle'=>'modal']) : $model->rank_points; ?>
                </td>
            </tr>
            <tr>
                <th>手机号</th><td><?= ((substr($model->mobile,0,1) == 'w') || substr($model->mobile,0,1) == 's') ? '' : $model->mobile; ?></td>
                <th>肤质</th><td><?= $userinfo['skin_name'] ?></td>
            </tr>
            <tr>
                <th>状态</th><td>
                    <?= Html::dropDownList('status',$model->status,['1' => '正常', '2'=> '禁言', '3'=> '封号'],['id'=>'status','style'=>'width:80px;']) ?>
                </td>
                <th>生日</th><td><?=(!empty($model->birth_year) && !empty($model->birth_month) && !empty($model->birth_day))? $model->birth_year.'.'.$model->birth_month.'.'.$model->birth_day:'未填写' ?></td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <?= (($model->img == 'photo/member.png' && $model->img_state == 0) ? "默认头像" :
                        ($model->img_state == 1 ? "<a href='javascript:void(0)' style='color:red'>已禁用</a>" :
                            Html::a('禁用', ['user/update','id'=> $model->id,'type'=>'imgState'],["class"=>"self"]) ))
                    ?>
                </td>
                <th>产品点评数</th><td><?= $userinfo['product_comment_num'] ?></td>
                <th>性别</th><td>
                    <?php
                        if($model->sex == 1){
                            echo "男";
                        }else if($model->sex == 0){
                            echo "女";
                        }else{
                            echo "未填";
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <th>用户ID</th><td><?=$model->id ?></td>
                <th>文章点评数</th><td><?= $userinfo['article_comment_num'] ?></td>
                <th>地区</th><td><?=!empty($model->city)?$model->city:'未填写' ?></td>
            </tr>
            <tr>
                <th>用户名</th><td><?=Tools::userTextDecode($model->username) ?><?= $model->remark ? "(".$model->remark.")" : "" ?>&emsp;&emsp;&emsp;&emsp;<span><?= HTML::a("备注",'javascript:void(0)',['id'=>'remarks']) ?></span><span class="remarkerror" style="color:red"></span></td>
                <th>注册时间</th><td><?=date('Y-m-d H:i:s',$model->created_at) ?></td>
                <th>用户沟通入口</th><td>
                    <?=Html::a('回复','#',[
                        'data' => [
                            'target' => '#file4',
                            'toggle' => 'modal',
                            'id' => $model->id,
                        ],
                        'class' => 'btn btn-default btn-xs create'
                    ]);?>&emsp;&emsp;  
                    <?=Html::a('记录','#',[
                        'data' => [
                            'target' => '#file5',
                            'toggle' => 'modal',
                            'id' => $model->id,
                        ],
                        'title' => '回复记录',
                    ]);?>
                </td>
            </tr>

        </table>
    </div>
<?php } ?>


<!-- 用户评论列表 -->
<?php
$items = [
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list"></span>',
        'content'=> $this->render('../comment/index', [
            'user_id' => $model->id,
//             'type' => '1',
            'jump_url' => Yii::$app->request->url
        ]),
        'active'=>true
    ],
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list"></span>',
        'content'=> $this->render('../ask/index', [
            'user_id' => $model->id,
            'data_id' => '',
            'jump_url' => Yii::$app->request->url
        ]),
        'active'=>false
    ],
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list"></span>',
        'content'=> $this->render('../user-product/index', [
            'user_id' => $model->id,
        ]),
        'active'=>false
    ],
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list"></span>',
        'content'=> $this->render('../user-inventory/index', [
            'user_id' => $model->id,
        ]),
        'active'=>false
    ],
    [
        'label'=>'<span style="font-weight:bold;font-size:17px" class="bar_list">反馈('.$userinfo['feedback_num'].')</span>',
        'content'=> $this->render('/user/_feedback', [
            'id' => $model->id,
        ]),
        'active'=>false
    ],
];
echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'encodeLabels'=>false,
    'id'=>'view-tab'
]);
?>

<!-- 查看图片弹窗 -->
<div class="modal fade" id="file">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">图片详情</h4>
            </div>
            <div class="modal-body" style="text-align:center">
                <img src="" id="image" class="kv-preview-data file-preview-image file-zoom-detail" style="width: 50%; height: 50%;">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- 颜值弹窗 -->
<div class="modal fade" id="file2">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">颜值记录页</h4>
            </div>
            <div class="modal-body">
                <?= $this->render('../user-account/index', [
                    'user_id' => $model->id,
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- 上传头像弹窗 -->
<div class="modal fade" id="file3">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">更换头像</h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'method' => 'post',
                    'action' => ["user/update?id=".$model->id."&type=image"],
                ]); ?>

                <?= $form->field($model, 'img')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：69x69',
                    ],
                ]) ?>
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-success']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!--回复弹框-->
<div class="modal fade" id="file4">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">创建回复</h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'method' => 'post',
                    'action' => ["user/communicate?id=$model->id"],
                ]); ?>

                <?= $form->field($model, 'communicate')->textarea(['rows' => 6])->label('')?>
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-success']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!--回复记录弹框-->
<div class="modal fade bs-example-modal-lg" id="file5">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">回复记录</h4>
            </div>
            <div class="modal-body" style="min-height:; max-height:1000px;overflow-y:auto;">
                <table class="table table-striped table-bordered detail-view">
                    <tbody>
                        <?php if (!empty($communicate)) {
                            foreach ($communicate as $key=>$val) {
                                echo "<tr><td>".$val->admin->username."</td><td>".$val->message."</td><td width='90px'>".date("Y-m-d H:m:s",$val->created_at)."</td></tr>";
                            }
                        } else {
                            echo "暂无记录";
                        }?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php

$script = <<<JS

// $(function(){
//     window.setTimeout(function(){
//         $(".close").click();
//     },1500);
// });

//修改生日
$(document).on("change",'#start_time',function(){
    var birth = $(this).val().trim();
    var url = "/user/update?id=$model->id&type=birth&birth="+ birth;
    window.location.href = url;
});


//修改状态
$(document).on("change",'#status',function(){
    var status = $(this).val();
    var url = "/user/update?id=$model->id&type=status&status="+ status;
    window.location.href = url;
});

//修改肤质
$(document).on("change",'#skin_id',function(){
    var skin_name = $(this).val();
    var url = "/user/update-skin?id=$model->id&skin_name="+ skin_name;
    window.location.href = url;
});

//备注框
$('#remarks').on('click', function(){
    var inputStr = "<input type='text' size='12' id='update_remark'/>";
    $(this).parent("span").html(inputStr);
});

//备注修改
$(document).on("blur",'#update_remark',function(){
    var remark = $(this).val().trim();
    
    if(remark.length > 10){
        $('.remarkerror').html("不能超过10个字符");
        return false;
        return false;
    } else {
        $('.remarkerror').html("");
    }
    
    var url = "/user/update?id=$model->id&type=remark&remark="+ remark;
    window.location.href = url;
});

//用户名框
$('#usernames').on('click', function(){
    var username = $(this).parents("td").find("span").eq(0).html();
    var inputStr = "<input type='text' size='20' id='update_username' value='"+ username +"'/>";
    $(this).parents("td").find("span").eq(0).html(inputStr);
    $(this).parents("td").find("span").eq(1).html("");
});

//用户名修改（马甲）
$(document).on("blur",'#update_username',function(){
    var username = $(this).val().trim();
    
    if(username.length > 10){
//         alert("马甲名不能超过10个字符");
//         $(this).focus();
        $('.nameerror').html("不能超过10个字符");
        return false;
    } else {
        $('.nameerror').html("");
    }
    
    if(username){
        var url = "/user/update?id=$model->id&type=username&username="+ username;
        window.location.href = url;
        $('.nameerror').html("");
    }else{
//         alert("马甲名不能为空");
//         $(this).focus();
        $('.nameerror').html("不能为空");
    }

});

//图片弹窗
$('img').on('click', function(){
    $('#image').attr('src', this.getAttribute("src"));
});

// $('#start_time').datepicker({
//     autoclose: true,
//     format : 'yyyy-m-d',
//     'language' : 'zh-CN',
// });

$(document).on("click",'#view-tab li',function(){
    var index = $(this).index();
    setCookie('types',index,10000);var types=getCookie('types');console.log(types);
});

$(function(){
    //评论数
    if ( $("#view-tab-container .summary").length > 0 ) {
        if($("#view-tab-container #grid-comment .summary").length > 0){
            var num = $("#grid-comment .summary").find("b").eq(1).text();
            var content = "用户评论("+ num +")";
            $("#view-tab-container").find(".bar_list").eq(0).html(content);
        } else {
            var content = "用户评论(0)";
            $("#view-tab-container").find(".bar_list").eq(0).html(content);
        }
                    
        if($("#view-tab-container #grid3 .summary").length > 0){
            var num = $("#view-tab-container #grid3 .summary").find("b").eq(1).text();
            var content = "问答("+ num +")";
            $("#view-tab-container").find(".bar_list").eq(1).html(content);
        } else {
            var content = "问答(0)";
            $("#view-tab-container").find(".bar_list").eq(1).html(content);
        }
                
        if($("#view-tab-container #grid5 .summary").length > 0){
            var num = $("#view-tab-container #grid5 .summary").find("b").eq(1).text();
            var content = "我在用的("+ num +")";
            $("#view-tab-container").find(".bar_list").eq(2).html(content);
        } else {
            var content = "我在用的(0)";
            $("#view-tab-container").find(".bar_list").eq(2).html(content);
        }
                
        if($("#view-tab-container #grid6 .summary").length > 0){
            var num = $("#view-tab-container #grid6 .summary").find("b").eq(1).text();
            var content = "我的清单("+ num +")";
            $("#view-tab-container").find(".bar_list").eq(3).html(content);
        } else {
            var content = "我的清单(0)";
            $("#view-tab-container").find(".bar_list").eq(3).html(content);
        }
                        
        if($("#view-tab-container #grid4 .summary").length > 0){
            var num = $("#view-tab-container #grid4 .summary").find("b").eq(1).text();
            var content = "反馈("+ num +")";
            $("#view-tab-container").find(".bar_list").eq(4).html(content);
        } else {
            var content = "反馈(0)";
            $("#view-tab-container").find(".bar_list").eq(4).html(content);
        }
                    
    } else {
        $('#view-tab-container').empty();
    }
                    
    //产品，文章评论、问答切换
    var types=getCookie('types'); console.log(types);
    if(types == '1'){
        $("#view-tab").find("li").eq(1).attr("class","active");
        $("#view-tab-tab1").addClass("active");
        $("#view-tab-tab1").addClass("in");
                    
        $("#view-tab").find("li").eq(0).attr("class","");
        $("#view-tab-tab0").removeClass("active");
        $("#view-tab-tab0").removeClass("in");
                    
        $("#view-tab").find("li").eq(2).attr("class","");  
        $("#view-tab-tab2").removeClass("active");
        $("#view-tab-tab2").removeClass("in");
                
        $("#view-tab").find("li").eq(3).attr("class","");
        $("#view-tab-tab3").removeClass("active");
        $("#view-tab-tab3").removeClass("in"); 
        
        $("#view-tab").find("li").eq(4).attr("class","");
        $("#view-tab-tab4").removeClass("active");
        $("#view-tab-tab4").removeClass("in"); 
    }else if(types == '2'){
        $("#view-tab").find("li").eq(2).attr("class","active");
        $("#view-tab-tab2").addClass("active");
        $("#view-tab-tab2").addClass("in");
        
        $("#view-tab").find("li").eq(1).attr("class","");
        $("#view-tab-tab1").removeClass("active");
        $("#view-tab-tab1").removeClass("in");
                    
        $("#view-tab").find("li").eq(0).attr("class","");
        $("#view-tab-tab0").removeClass("active");
        $("#view-tab-tab0").removeClass("in");
                
        $("#view-tab").find("li").eq(3).attr("class","");
        $("#view-tab-tab3").removeClass("active");
        $("#view-tab-tab3").removeClass("in"); 
                
        $("#view-tab").find("li").eq(4).attr("class","");
        $("#view-tab-tab4").removeClass("active");
        $("#view-tab-tab4").removeClass("in");                
    }else if(types == '3'){
        $("#view-tab").find("li").eq(3).attr("class","active");
        $("#view-tab-tab3").addClass("active");
        $("#view-tab-tab3").addClass("in");
        
        $("#view-tab").find("li").eq(1).attr("class","");
        $("#view-tab-tab1").removeClass("active");
        $("#view-tab-tab1").removeClass("in");
                    
        $("#view-tab").find("li").eq(2).attr("class","");
        $("#view-tab-tab2").removeClass("active");
        $("#view-tab-tab2").removeClass("in");    
                
        $("#view-tab").find("li").eq(0).attr("class","");
        $("#view-tab-tab0").removeClass("active");
        $("#view-tab-tab0").removeClass("in"); 
                
        $("#view-tab").find("li").eq(4).attr("class","");
        $("#view-tab-tab4").removeClass("active");
        $("#view-tab-tab4").removeClass("in"); 
    }else if(types == '4'){
        $("#view-tab").find("li").eq(4).attr("class","active");
        $("#view-tab-tab4").addClass("active");
        $("#view-tab-tab4").addClass("in");
        
        $("#view-tab").find("li").eq(1).attr("class","");
        $("#view-tab-tab1").removeClass("active");
        $("#view-tab-tab1").removeClass("in");
                    
        $("#view-tab").find("li").eq(2).attr("class","");
        $("#view-tab-tab2").removeClass("active");
        $("#view-tab-tab2").removeClass("in");    
                
        $("#view-tab").find("li").eq(0).attr("class","");
        $("#view-tab-tab0").removeClass("active");
        $("#view-tab-tab0").removeClass("in");  
                
        $("#view-tab").find("li").eq(3).attr("class","");
        $("#view-tab-tab3").removeClass("active");
        $("#view-tab-tab3").removeClass("in");             
    }else{
        $("#view-tab").find("li").eq(0).attr("class","active");
        $("#view-tab-tab0").addClass("active");
        $("#view-tab-tab0").addClass("in");
        
        $("#view-tab").find("li").eq(1).attr("class","");
        $("#view-tab-tab1").removeClass("active");
        $("#view-tab-tab1").removeClass("in");
                    
        $("#view-tab").find("li").eq(2).attr("class","");
        $("#view-tab-tab2").removeClass("active");
        $("#view-tab-tab2").removeClass("in");
                
        $("#view-tab").find("li").eq(3).attr("class","");
        $("#view-tab-tab3").removeClass("active");
        $("#view-tab-tab3").removeClass("in"); 
                
        $("#view-tab").find("li").eq(4).attr("class","");
        $("#view-tab-tab4").removeClass("active");
        $("#view-tab-tab4").removeClass("in");               
    }
});

JS;
$this->registerJs($script);
?>