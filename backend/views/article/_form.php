<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ProductCategory;
use common\models\ArticleCategory;
use kartik\select2\Select2;
use backend\models\CommonFun;
use common\models\CommonTag;
use common\functions\Functions;
use yii\web\JsExpression;
use backend\assets\AppAsset;
AppAsset::register($this);

//分类
$cateid = ProductCategory::find()->asArray()->all();
$cateList = [];
$cateList['0'] = '未设置';
foreach ($cateid as $key=>$val) {
    $cateList[$val['id']] = $val['cate_name'];
}
?>

<div class="article-form">
    <?php $form = ActiveForm::begin(); ?>
    <div>
        <div style="width:300px;float:left;">
            <?= $form->field($model, 'article_img')->widget('common\widgets\file_upload\FileUpload',[
                'config'=>[
                    'domain_url' => Yii::$app->params['uploadsUrl'],
                    'explain' => '<b>推荐尺寸：</b>648*405',
                ],
            ]) ?>
        </div>

        <div style="width:50%;float:left;">
            <?= $form->field($model, 'title')->textInput(['style'=>'width:320px;']) ?>
            
            <?php 
            //编辑或创建
            if (isset($tagIdArr)) {
                $tagData = CommonFun::getKeyValArr(new CommonTag(), 'tagid', 'tagname',Functions::db_create_in($tagIdArr,'tagid'));
                $model->tag_name =  $tagIdArr;
            } else {
                $tagData = [];
            }
            //新标签
            echo '<input type="hidden" class="new_tag" name="Article[new_tag]" value="">';
            echo "<div style='width:320px'>".$form->field($model, 'tag_name', ['labelOptions' => ['label' => '关键词<span style="color:red">(最多5个)</span>','class' => 'control-label']])->widget(Select2::classname(), [
                'options' => ['placeholder' => '请输入关键词 ...','multiple' => true],
                'data' => $tagData,
                'showToggleAll' => false,
                'maintainOrder' => true,
                'pluginOptions' => [
                    'placeholder' => 'Waiting...',
                    'language'=>"zh-CN",
                    'minimumInputLength'=> 1,
                    'maximumInputLength'=> 20,
                    'tags' => true,
                    'ajax' => [
                        'url' => '/product-details/search-tag?type=2',
                        'dataType' => 'json',
                        'cache' => true,
                        'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])."</div>";
            ?>
            
            <?= $form->field($model, 'skin_id')->dropDownList( $skinList,['style'=>'width:250px;','prompt'=>'请选择'])->label("针对肤质") ?>
            
            <div style="float:left;margin-right:20px;">
                <?= $form->field($model, 'status')->dropDownList(['1'=>'上架','0'=>'下架'],['style'=>'width:120px;']) ?>
            </div>
            <div style="float:left;margin-right:20px;">
                <?= $form->field($model, 'is_recommend')->dropDownList(['0'=>'否','1'=>'是'],['style'=>'width:120px;']) ?>
            </div>
            <div style="float:left;">
                <?= $form->field($model, 'stick')->dropDownList(['0'=>'否','1'=>'是'],['style'=>'width:120px;']) ?>
            </div>
            <div style="clear:both;"></div>

            <?php
            //编辑页分类展示
            if(!$model->isNewRecord){
                $parent_id = ArticleCategory::find()->select("parent_id")->where(['id'=>$model->cate_id])->asArray()->scalar();
                //有二级分类
                if($parent_id){
                    $model->cate_ids = $parent_id;
                    $list2 = yii\helpers\ArrayHelper::map(ArticleCategory::find()->where(['parent_id'=>$parent_id])->all(), 'id', 'cate_name');
                    //只有一级分类
                }else{
                    $model->cate_ids = $model->cate_id;
                    $list2 = yii\helpers\ArrayHelper::map(ArticleCategory::find()->where(['parent_id'=>$model->cate_id ])->all(), 'id', 'cate_name');
                }
            }
            ?>

            <div style="float:left;margin-right:20px;">
                <?= $form->field($model, 'cate_ids')->dropDownList( yii\helpers\ArrayHelper::map(ArticleCategory::find()->where(['parent_id'=>0])->all(), 'id', 'cate_name'),['style'=>'width:120px;','prompt'=>'--- 请选择 ---'])->label("文章一级分类") ?>
            </div>

            <div style="float:left;">
                <?= $form->field($model, 'cate_id')->dropDownList( isset($list2) ? $list2 : "",['style'=>'width:120px;','prompt'=>'--- 请选择 ---'])->label("文章二级分类") ?>
            </div>
        </div>
    </div>
    <div style="clear:both;margin-bottom: 20px;"></div>

    <div class="form-group field-article-content required" style="clear:both;">
        <label class=" control-label" for="article-content">文章内容</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <?php //echo  Html::a('<i class="glyphicon glyphicon-plus"></i> 添加产品','javascript:void(0)', ['class' => 'btn btn-success btn-sm sel_product','data-target'=>'#comment','data-toggle'=>'modal',"style"=>"margin-bottom:10px;"]) ?>
            <?php //echo  Html::a('<i class="glyphicon glyphicon-minus"></i> 删除产品','javascript:void(0)', ['class' => 'btn btn-danger btn-sm del_product','data-target'=>'#del','data-toggle'=>'modal',"style"=>"margin-left:20px;margin-bottom:10px;"]) ?>
            <?php echo kucha\ueditor\UEditor::widget([ 'model' => $model, 'attribute' => 'content' ,
                'clientOptions' => [
                    //'initialFrameHeight' => '800',
                    'initialFrameWidth' => '92%',
                    'lang' =>'zh-cn',
                    'allowDivTransToP' => false,
                    'wordCount' => false,
                    'elementPathEnabled' => false,
                    'toolbars' => [
                        [
                            'source', 'undo', 'redo', 'bold', 'indent', 'italic', 'underline', 'strikethrough', 'formatmatch', 'pasteplain', 'selectall', 'horizontal', 'removeformat', 'cleardoc', 'fontfamily',
                            'fontsize', 'paragraph', 'simpleupload', 'insertimage', 'emotion', 'spechars', 'searchreplace', 'map', 'justifyleft', 'justifyright', 'justifycenter', 'justifyjustify', 'forecolor',
                            'backcolor', 'insertorderedlist', 'insertunorderedlist', 'fullscreen', 'rowspacingtop', 'rowspacingbottom', 'lineheight', 'edittip ', 'autotypeset', 'background', 'template', 'customstyle',
                            'imagenone', 'imageleft','imageright', 'imagecenter', 'link',
                        ]
                    ]
                ] ])
            ?>
        <div class="help-block"></div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认创建' : '保存', ['class' => $model->isNewRecord ? 'btn btn-success new' : 'btn btn-success checks','style' => 'width:100px']) ?>&nbsp;&nbsp;&nbsp;
        <a href="javascript:void(0)" class="btn btn-default preview" style="width:100px;">预览</a>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!--添加产品弹框-->
<div class="modal fade bs-example-modal-lg" id="comment">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">产品列表</h4>
            </div>
            <div class="modal-body">
                <?php Pjax::begin([
                    'enablePushState' => false,
                    'timeout'         => 10000,
                ])?>
                <div class="product-details-index">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        "options" => [
                            "id" => "grid"
                        ],
                        'columns' => [
                            [
                                "class" => 'yii\grid\CheckboxColumn',
                                "name" => "id",
                                "header"=>'<span style="display:;"><input class="select-on-check-all" name="id_all" value="1" type="checkbox"> 全选</span>',
                                'headerOptions' => ['width' => '8%'],
                                'contentOptions' => ['class'=>'checkboxs'],
                            ],
                            [
                                'attribute' => 'id',
                                'options' => ['width' => '12%'],
                            ],
                            [
                                'attribute' => 'product_name',
                                'format' => 'raw',
                                'value' => function($model){
                                    return Html::a($model->product_name,['product-details/view','id'=>$model->id],['data-pjax'=>'false','target'=>'_blank']);
                                }
                            ],
//                            'brand',
                            [
                                'attribute' => 'cate_id',
                                'value'     => function($model){
                                    return empty($model->productCategory->cate_name) ? '未设置' : $model->productCategory->cate_name;
                                },
                                'filter' => Html::activeDropDownList($searchModel,
                                    'cate_id',$cateList,
                                    ['prompt' => '所有'])
                            ],
                            [
                                'attribute' => 'price',
                                'options' => ['style' => 'width:70px'],
                                'value' => function($model) {
                                    return empty($model->price) ? '' : $model->price;
                                }
                            ],
                            'form',
                            [
                                'attribute' => 'star',
                                'format' => 'raw',
                                'value' => function($model){
                                    $stars = '';
                                    for($i=0;$i<$model->star;$i++){
                                        $stars .= "<span class='star-active-icon'></span>";
                                    }
                                    if ($model->star < 5) {
                                        for($i=0;$i<5-($model->star);$i++){
                                            $stars .= "<span class='star-icon'></span>";
                                        }
                                    }
                                    return $stars;
                                },
                                'filter' => Html::activeDropDownList($searchModel,
                                    'star',['1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星',],
                                    ['prompt' => '所有'])
                            ],
                        ],
                    ]); ?>
                </div>
                <?php Pjax::end()?>

                <div style="width:300px;height:120px;">
                    <div style="clear:both;">
                        <?= Html::a("确认添加", "javascript:void(0);", ["class" => "btn btn-success select-product"]) ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;<span id="product_num"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--删除产品弹框-->
<div class="modal fade bs-example-modal-lg" id="del">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">删除产品</h4>
            </div>
            <?php Pjax::begin()?>
            <div class="modal-body">
            </div>
            <?php Pjax::end()?>
        </div>
    </div>
</div>

<div style="display:none" class="box"></div>

<!--预览弹框-->
<div style="display:none" id="preview-box">
    <h2></h2>
    <div class="article-ctit">
        <span><?= date("Y/m/d") ?></span><span>颜究院</span>
    </div>
    <div class="article-data">
    </div>
</div>

<!--右侧导航框-->
<div class="izl-rmenu">
    <a href="javascript:void(0)" class="sel_product" data-target="#comment" data-toggle="modal">
        <div></div><span>添加产品</span>
    </a>
    <a href="javascript:void(0)" class="del_product" data-target="#del" data-toggle="modal">
        <div></div><span>删除产品</span>
    </a>
    <a href="javascript:void(0)" class="btn_top" >
        <div></div><span>回到顶部</span>
    </a>
</div>


<div id="sel_id" style="display: none;"></div>
<?php
AppAsset::addScript($this,'@web/js/artDialog4.1.6/jquery.artDialog.source.js?skin=idialog');

$script = <<<JS
//记录全选
$(document).on("click",'#comment .select-on-check-all',function(){
    setTimeout(function(){
        check_list('#w1','#sel_id');
    },100);
});
//记录多选
$(document).on("click",'#comment .checkboxs input',function(){
    check_list('#w1','#sel_id');
});

//已经添加的产品id数组
function del_list(){
    var content = ue.getContent();
    $(".box").html(content);
    
    var id = new Array();
    id[0] = 0;
    $(".box .data").each(function(i){
        id[i] = parseInt($(this).html());
    });
    return id;
}

//添加产品按钮 (排除已经添加的产品)
$(".sel_product").on('click', function() {
    del_ids = del_list();
    var url = changeURLArg(window.location.href, 'ids', del_ids); 
    url = changeURLArg(url, 'page', 1); 
    var options = {url: url,timeout:0}
    $.pjax.reload('#w1', options);
});

//删除产品按钮 （已选择产品列表）
$(document).on("click",'.del_product',function(){
    del_ids = del_list();
    
    //console.log(id);
    var url = '/product-details/article-index';
    var type = 'sel';
    $.get(url, {del_ids: del_ids},
        function (data) {
            $("#del").find(".modal-body").html(data);
        }  
    );
});

//pjax请求之前调用
$(document).on('pjax:send', function() {
    check_list('#w1','#sel_id');
});

//pjax请求成功之后调用
$(document).on('pjax:complete', function() {
   var old =  $("#sel_id").html();
   if(old){
       old = old.split("-");

       //处理已经选择的数据
       $("#w1 input[name='id[]']").each(function(){
           if(inArray(old,parseInt($(this).val()) )){
               $(this).attr("checked",true);
           }
       })
       
   }
});

//百度编辑器初始化
var ue = UE.getEditor('article-content');
// ue.addListener("keydown",function(type,event){
// })
  
//批量删除产品
$(document).on("click",'.delete-product',function(){
    var arr = $("#grid-del").yiiGridView("getSelectedRows");

    if(arr == ""){
        alert("请选择商品");
        return false;
    }
    
    $(".box .data").each(function(i){
        for(var i in arr){
            if($(this).html() == arr[i]){
                $(this).parents(".border").remove();
            }
        }
    });

    content = $(".box").html();
    ue.setContent(content);
    $("#del").find(".close").click();
});

//批量选择产品
$(document).on("click",'.select-product',function(){
    //var id = $("#grid").yiiGridView("getSelectedRows");
    //check_list();

    var id =  $("#sel_id").html();
    id = id.split("-");

    if(id == ""){
        alert("请选择商品");
        return false;
    }
    
     $.ajax({
        url: '/article/select-product',
        type: 'post',
        dataType: 'json',
        data:{id:id},
        success : function(data) {
            ue.execCommand('inserthtml', data);
            //ue.setContent(data, true);
            //$("#comment").find(".close").click();
            
            //排除已经添加的产品
            del_ids = del_list();
            var url = changeURLArg(window.location.href, 'ids', del_ids); 
            var options = {url: url,timeout:0}
            $.pjax.reload('#w1', options);
            $("#sel_id").html("");
            
            $("#product_num").html("");
            
            $("input[name='id[]']").attr("checked", false);
            
        },
        error : function(data) {
            alert('操作失败！');
        }
    });
    
});

//提交前处理
$('.new').on('click', function (e) {
    
    //判断内容是否为空
    var check = ue.hasContents();
    var title = $("#article-title").val();
    if(check == false){
        $(".field-article-content").addClass("has-error");
        $(".field-article-content").find(".help-block").html("  文章内容不能为空。");
        return false;
    }else{
        //批量处理图片title,alt
        var content = ue.getContent();
        $(".box").html(content);
        $(".box img").each(function(i){
            $(this).attr("title",title + (i+1) );
            $(this).attr("alt",title + (i+1) );
        });
        
        content = $(".box").html();
        ue.setContent(content);
        $(".box").html("");
    
    }
    
    //$(':submit').attr('disabled', true).addClass('disabled');
});

//文章预览modal
 $('.preview').on('click', function () {
    var content = ue.getContent();
    var title = $("#article-title").val();
    
    $(".article-data").html(content);
    $("#preview-box h2").html(title);
    content = $("#preview-box").html();
    
    art.dialog({
        content : content,
        width : "710px",
        title : '文章预览',
        padding : "40px 70px",
        lock : true,
        opacity : .4,
        time : 50,
        top: '5%',				// Y轴坐标
    });
});

//获取2级分类
$(document).on("change",'#article-cate_ids',function(){
    var cate_id = $("#article-cate_ids").val();
    $.get('/article-category/get-cate-list',{cate_id: cate_id},function(data){
        $("#article-cate_id").html(data);
    });
});

//关键词
$('form').on('beforeSubmit', function (e) {
    var new_tag_option = [];
    var old_tag_option = [];
    var new_tag = '';
    
    $('#article-tag_name option').each(function(index) {
        if($(this).attr('data-select2-tag') == 'true'){
            new_tag_option[index] = $(this).val();
        }else{
            old_tag_option[index] = $(this).html();
        }
    });
    
    $('.select2-container .select2-selection__rendered li').each(function() {
        if(($.inArray($(this).attr('title'), new_tag_option) != -1) && ($.inArray($(this).attr('title'), old_tag_option) == -1)){
            new_tag += $(this).attr('title')+',';
        }           
    });
    
    $('#article-tag_name option').each(function() {
        if($(this).attr('data-select2-tag') == 'true'){
            $(this).remove();
        }
    });
    $('.new_tag').val(new_tag);
});

JS;
$this->registerJs($script);
?>
