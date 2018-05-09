<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */


if (Yii::$app->controller->action->id === 'login') { 
/**
 * Do not use this code in your template. Remove it. 
 * Instead, use the code  $this->layout = '//main-login'; in your controller.
 */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
    <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
<script type="text/javascript">

//动态加载页数
$(document).on("change",'select[name="pages"]',function(){
    var pages = $(this).val();
    var url = "index?pages=" + pages;
    window.location.href = url;
});

$(function(){
    //调整样式
    if($(".content ul").hasClass("pagination")){
        $(".summary").css("float",'right');
    }

    //时间搜索判断
    var sdate = new Date($("input[name='start_at']").val());
    var edate = new Date($("input[name='end_at']").val());
    var stime = sdate.getTime();
    var etime = edate.getTime();
    
    if (stime > etime) {
        $("input[name='start_at']").parent("td").addClass('has-error');
        var data = "<div class='help-block'>起始时间不能大于结束时间</div>";
    }
    $("input[name='start_at']").parent("td").append(data);


    //回到顶部按钮
    $(".btn_top").hide();
    $(document).on("click",'.btn_top',function(){
        $('html, body').animate({scrollTop: 0},300);return false;
    })

    $(window).bind('scroll resize',function(){
        if($(window).scrollTop()<=300){
            $(".btn_top").hide();
        }else{
            $(".btn_top").show();
        }
    })

    //左侧菜单栏
//     $(window).scroll(function(){
//   	　　var scrollTop = $(this).scrollTop();
//   	　　var scrollHeight = $(document).height();
//   	　　var windowHeight = $(this).height();
//   	　　if(scrollTop + windowHeight == scrollHeight){
//  	    $(".main-sidebar").css("padding-top",""+scrollTop+"");
// 			$(".main-sidebar").css("position","absolute");
//   	　　}else if($(window).scrollTop() > 0){
//   			$(".main-sidebar").css("padding-top","0");
//   			$(".main-sidebar").css("position","fixed");
//   		}else{
//   			$(".main-sidebar").css("padding-top","0");
//   			$(".main-sidebar").css("position","absolute");
//   		}
//   	});
})

//超链接新窗口打开
var anchors = $('.table td').find('a');
for (var i=0; i<anchors.length; i++) {
    var anchor = anchors[i];
    if(($('.table td').find('a').eq(i).attr("href") == "javascript:void(0)") || $('.table td').find('a').eq(i).attr("title") == "删除" || $('.table td').find('a').eq(i).hasClass("self")){
        anchor.target = "";
    }else{
        anchor.target = "_blank";
    }
}
$('.glyphicon .glyphicon-trash').attr('target','');
$('.btn').attr('target','');
$('.noself').attr('target','_blank');

//js cookie 设置(分钟)
function setCookie(c_name,value,expiredays){
    var exdate= new Date();
    exdate.setTime(exdate.getTime() + (expiredays * 60 * 1000));

    document.cookie=c_name+ "=" +escape(value)+
        ((expiredays==null) ? "" : ";expires="+exdate.toUTCString())
}

function getCookie(c_name){
    if (document.cookie.length>0){
        c_start=document.cookie.indexOf(c_name + "=")
        if (c_start!=-1){
            c_start=c_start + c_name.length+1
            c_end=document.cookie.indexOf(";",c_start)
            if (c_end==-1) c_end=document.cookie.length
            return unescape(document.cookie.substring(c_start,c_end))
        }
    }
    return ""
}

//替换url参数
function changeURLArg(url,arg,arg_val){
    var pattern=arg+'=([^&]*)';
    var replaceText=arg+'='+arg_val;
    if(url.match(pattern)){
        var tmp='/('+ arg+'=)([^&]*)/gi';
        tmp=url.replace(eval(tmp),replaceText);
        return tmp;
    }else{
        if(url.match('[\?]')){
            return url+'&'+replaceText;
        }else{
            return url+'?'+replaceText;
        }
    }
    return url + arg + arg_val;
}

//判断字符串是否存在数组里
var inArray = function(arr, item) {
    for (var i = 0; i < arr.length; i++) {
        if (parseInt(arr[i]) == item) {
            return i+1;
        }
    }
    return false;
};

//获取评论未读数
// $(function(){
//     $(".sidebar li").each(function(i) {
//         if($(this).attr("icon") == "fa fa-briefcase") {
//             var url = "/comment/unread-num";
//             var _this = $(this);
//             $.get(url, {},
//                 function (data) {
//                     if(data != 0){
//                         var html = "<span class='label pull-right bg-red'>" + data + "</span>";
//                         	_this.find("a:eq(0)").append("<span class=' pull-right bg-red' style='border-radius: 50%;width:10px;height:10px;margin-right:90px;margin-top:-4px'> </span>");

//                         _this.find("ul li a").each(function(i) {
// 							if ($(this).attr('href') == "/comment/index") {
// 								$(this).append(html);
// 							}
//                         })
//                     }
//                 }
//             );
//         }
//     });
// });

//获取未读数
$(function(){
    $(".sidebar li").each(function(i) {
        //评论
        if($(this).attr("icon") == "fa fa-commenting-o"){
            var url = "/comment/unread-num";
            var _this = $(this);
            $.get(url, {},
                function (data) {
                    if(data != 0){
                        var html = "<span class='label pull-right bg-red'>" + data + "</span>";
                        _this.find("a").append(html);
                    }
                }
            );
        }
        //问答
        if($(this).attr("icon") == "fa fa-briefcase") {
            var url = "/ask/unread-num";
            var _this = $(this);
            $.get(url, {},
                function (data) {
                    if(data != 0){
                        var html = "<span class='label pull-right bg-red'>" + data + "</span>";
                        	_this.find("a:eq(0)").append("<span class=' pull-right bg-red' style='border-radius: 50%;width:10px;height:10px;margin-right:90px;margin-top:-4px'> </span>");

                        _this.find("ul li a").each(function(i) {
							if ($(this).attr('href') == "/ask/index") {
								$(this).append(html);
							}
                        })
                    }
                }
            );
        }
        //用户反馈
        if($(this).attr("icon") == "fa   fa-paper-plane-o"){
            var url = "/user-feedback/notice-num";
            var _this = $(this);
            $.get(url, {},
                function (data) {
                    if(data != 0){
                        var html = "<span class='label pull-right bg-red'>" + data + "</span>";
                        _this.find("a").append(html);
                    }
                }
            );
        }
        //帖子
        if($(this).attr("icon") == "fa fa-coffee") {
            var url = "/post/unread-num";
            var _this = $(this);
            $.get(url, {},
                function (data) {
                    if(data != 0){
                        var html = "<span class='label pull-right bg-red'>" + data + "</span>";
                        	_this.find("a:eq(0)").append("<span class=' pull-right bg-red' style='border-radius: 50%;width:10px;height:10px;margin-right:90px;margin-top:-4px'> </span>");

                        _this.find("ul li a").each(function(i) {
							if ($(this).attr('href') == "/post/index") {
								$(this).append(html);
							}
                        })
                    }
                }
            );
        }
    });
});

</script>