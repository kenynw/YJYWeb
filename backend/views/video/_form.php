<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\base\Object;
use backend\models\CommonFun;
use kartik\select2\Select2;
use yii\web\JsExpression;
use common\models\ProductDetails;
use common\functions\Functions;

/* @var $this yii\web\View */
/* @var $model common\models\Video */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="video-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    
    <div style="width:80%;">
        <div style="display: none">
            <?= $form->field($model, 'video')->textInput([]) ?>
        </div>
        (文件名不能含有中文)
        <?php $file = empty($model->video) ? '' : '（已存在视频：'.substr($model->video,0,50).'）'; ?>
        <?= $form->field($model, 'file',['labelOptions' => ['label' => '视频'.$file]])->fileInput([]) ?>
        <span id="file-pro" style="color: red"></span>
        <br>
        <br>
        <?= $form->field($model, 'title')->textInput([]) ?>
        
        <?= $form->field($model, 'desc')->textarea(['rows' => 4]) ?>

        <div style="float:left;width:160px;">
            <?= $form->field($model, 'status')->dropDownList(['1'=>'上线','0'=>'下线'],['style'=>'width:120px;']) ?>
        </div>
        <div style="float:left;margin-left:12.5%;width:30%;">
            <?= $form->field($model, 'duration')->textInput(['value' => $model->isNewRecord ? '00:00' : $model->duration,'style' => ['font-weight'=>'bold']])->label('时长（格式需为 00:00）') ?>
        </div>            

        <div style="clear:both;">
            <div style="float:left;">
                <?= $form->field($model, 'thumb_img')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：750*422',
                    ],
                ]) ?>
            </div>   
            
            <div style="float:left;margin-left:100px;">
                <?= $form->field($model, 'icon_img')->widget('common\widgets\file_upload\FileUpload',[
                    'config'=>[
                        'domain_url' => Yii::$app->params['uploadsUrl'],
                        'explain' => '推荐尺寸：120*120',
                    ],
                ]) ?>
            </div>      
        </div>
        <!-- 相关产品 -->     
        <div style="clear:both;">                              
            <?php 
            $productData = $model->isNewRecord ? '' : CommonFun::getKeyValArr(new ProductDetails(), 'id', 'product_name',Functions::db_create_in(explode(',', $model->product_id),'id'));
            $model->product_id =  $model->isNewRecord ? '' : explode(',', $model->product_id);
            
            echo $form->field($model, 'product_id')->widget(Select2::classname(), [
                           'options' => ['placeholder' => '请输入产品名称 ...','multiple' => true],
                           'data' => $productData,
                           'showToggleAll' => false,
                           'maintainOrder' => true,
                           'pluginOptions' => [
                               'placeholder' => 'Waiting...',
                               'language'=>"zh-CN",
                               'minimumInputLength'=> 1,
                               'ajax' => [
                                   'url' => '/ranking/search-product',
                                   'dataType' => 'json',
                                   'cache' => true,
                                   'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                               ],
                               'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                               'templateResult' => new JsExpression('function(res) { return res.text; }'),
                               'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                           ],
                    ]);
            ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '保存', ['class' => 'btn btn-success sub']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?=Html::jsFile("http://gosspublic.alicdn.com/aliyun-oss-sdk-4.3.0.min.js")?>
<?php 
$uploadsUrl = Yii::$app->params['isOnline'] ? 'uploads/' : 'cs/uploads/';
$apiPathUrl = Yii::$app->params['apiPathUrl'];
$script = <<<JS
$(function(){
    //文件
    $('.sub').on('click', function () { 
//         if($("#video-file").val() != "") {
//             $("#video-file").parent().removeClass('has-error');
//             $("#video-file").next().html('');
//         } else {
//             $("#video-file").parent().addClass('has-error');
//             $("#video-file").next().html('视频不能为空');
//             return false;
//         }
        if($("#file-pro").html() == "" && $("#video-video").val() == "") {
            $("#video-file").parent().addClass('has-error');
            $("#video-file").next().html('视频不能为空');
            return false;
        } else {
            $("#video-file").parent().removeClass('has-error');
            $("#video-file").next().html('');            
        }
    });
    
    //清空file input
    $('form').on('beforeSubmit', function (e) {
        document.getElementById("video-file").value = "";
    })
    
    //百分比
    function toPercent(point){
        var str=Number(point*100).toFixed(2);
        str+="%";
        return str;
    }
    
    //上传视频
    var d       = new Date();
    var time    = d.getFullYear().toString()+(d.getMonth()+1).toString()+d.getDate().toString();

    document.getElementById('video-file').addEventListener('change', function (e) {
      var file = e.target.files[0];
      var storeAs = '$uploadsUrl'+'videos/'+time+'/'+file.name;
      var path = 'videos/'+time+'/'+file.name;
      
      //验证格式
      var point = file.name.lastIndexOf(".");   
      var type = file.name.substr(point);
      var execarr = [".avi",".rmvb",".rm",".asf",".divx",".mpg",".mpeg",".mpe",".wmv",".mp4",".mkv",".vob"];
      var result = $.inArray(type, execarr);
      if (result == '-1') {  
          alert("文件格式不对，可为以下几种（*.avi *.rmvb *.rm *.asf *.divx *.mpg *.mpeg *.mpe *.wmv *.mp4 *.mkv *.vob）");  
          document.getElementById("video-file").value = "";
          return false;  
      }
      //验证文件名
      var myReg = /^[a-zA-Z0-9_.]{0,}$/;  
      if (!myReg.test(file.name)) {  
          alert("文件名不能含有中文或特殊字符");  
          document.getElementById("video-file").value = "";
          return false;  
      }  
      
      OSS.urllib.request("$apiPathUrl"+"/oss-sts/sts.php",
          {method: 'GET'}, 
          function (err, response) {
          if (err) {
            return alert(err);
          }
          try {
            result = JSON.parse(response);
          } catch (e) {
            return alert('parse sts response info error: ' + e.message);
          }
          var client = new OSS.Wrapper({
            accessKeyId: result.AccessKeyId,
            accessKeySecret: result.AccessKeySecret,
            stsToken: result.SecurityToken,
            endpoint: 'oss-cn-shenzhen.aliyuncs.com',
            bucket: 'oss1-yjyapp-com'
          });
          client.multipartUpload(storeAs, file, {
                progress: function* (p) {
                  console.log('Progress: ' + p);
            console.log(toPercent(p));
                  $("#file-pro").html("上传进度："+toPercent(p));
                }
            }).then(function (result) {
            console.log(result);
            
            document.getElementById("video-video").value = ""+path+"";

          }).catch(function (err) {
            console.log(err);
          });

            
            
        });
    });
})
JS;
$this->registerJs($script);
?>