<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title></title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <link rel="stylesheet" type="text/css" href="<?=Yii::$app->params['static_path']?>h5/swoole/css/style.min.css">
    <link rel="stylesheet" type="text/css" href="<?=Yii::$app->params['static_path']?>h5/swoole/css/zoom.css">
</head>
<body>
<div class="adminChat">
    <div class="empty-chat" id="empty-chat">
        <img src="<?=Yii::$app->params['static_path']?>h5/swoole/images/none-img.png" alt="">
        <p>说说你的建议吧！</p>
    </div>
    <ul class="chatroom-box" id="messages">
    </ul>
    <!-- <div class="imgZoom">
        <img src="">
    </div> -->
    <div id="jdw">&nbsp;</div>
    <div class="inp-box">
        <form action="" role="form" onsubmit="sendMessage(27,<?=Yii::$app->user->id?>); return false;">
            <input type="text" name="message" placeholder="" class="inp-text" id="message">
            <input type="file" name="" class="imgfile" id="imgfile" onClick="uploadImg()">
            <input type="hidden" value="" name="image"  />
            <input type="hidden" id="userId" value="<?=$user_id?>"/>
            <input type="hidden" id="userImg" value="<?=$user_img?>"/>
            <input type="hidden" id="adminId" value="<?=Yii::$app->user->id?>"/>
            <input type="hidden" id="fId" value="1"/>
            <span class="imgfile-bg"></span>
            <input type="button" name="" value="发&nbsp;送" class="submit-btn" onClick="sendMessage(27,<?=Yii::$app->user->id?>)">
        </form>
    </div>
</div>
<script type="text/javascript" src="<?=Yii::$app->params['static_path']?>h5/swoole/js/lib/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="<?=Yii::$app->params['static_path']?>h5/swoole/js/lib/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="<?=Yii::$app->params['static_path']?>h5/swoole/js/lib/zoom.min.js"></script>
<script>
    var static_path = "<?=Yii::$app->params['static_path']?>";
    var apiPathUrl  = "<?=Yii::$app->params['apiPathUrl']?>";
    var swooleIp    = "<?php if(Yii::$app->params['isOnline']){echo "ws://112.74.63.107:9502";}else{ echo "ws://192.168.100.1:9502";}?>";
</script>
<script type="text/javascript" src="<?=Yii::$app->params['static_path']?>h5/swoole/js/web-page.js"></script>
<script type="text/javascript" src="<?=Yii::$app->params['static_path']?>h5/swoole/js/web-socket.js"></script>
</body>
</html>
