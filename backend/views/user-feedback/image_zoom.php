<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title></title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
    <style>
    *{ margin:0; padding:0;}
    body {background: #333;}
    #pageContent {width: 980px; height: 500px;overflow: hidden;position:relative;margin:50px auto;}
    #imgContainer {width: 980px;height: 500px;}
    #positionButtonDiv {background: rgb(58, 56, 63);background: rgba(58, 56, 63, 0.8);border: solid 1px #100000;color: #fff;padding: 8px;text-align: left;position: absolute;right:35px;top:35px;}
    #positionButtonDiv .positionButtonSpan img {float: right;border: 0;}
    .positionMapClass area {cursor: pointer;}
    .zoomButton {border: 0; cursor: pointer;}
    .zoomableContainer {background-image: url("<?=Yii::$app->params['static_path']?>h5/swoole/images/transparent.png");}
    </style>
</head>
<body>
<div id="pageContent">
    <div id="imgContainer">
        <img id="imageFullScreen" src=""/>
    </div>
    <div id="positionButtonDiv">
        <p><span> <img id="zoomInButton" class="zoomButton" src="<?=Yii::$app->params['static_path']?>h5/swoole/images/zoomIn.png" title="zoom in" alt="zoom in" /> <img id="zoomOutButton" class="zoomButton" src="<?=Yii::$app->params['static_path']?>h5/swoole/images/zoomOut.png" title="zoom out" alt="zoom out" /> </span> </p>
        <p>
        <span class="positionButtonSpan">
            <map name="positionMap" class="positionMapClass">
                <area id="topPositionMap" shape="rect" coords="20,0,40,20" title="move up" alt="move up"/>
                <area id="leftPositionMap" shape="rect" coords="0,20,20,40" title="move left" alt="move left"/>
                <area id="rightPositionMap" shape="rect" coords="40,20,60,40" title="move right" alt="move right"/>
                <area id="bottomPositionMap" shape="rect" coords="20,40,40,60" title="move bottom" alt="move bottom"/>
            </map>
            <img src="<?=Yii::$app->params['static_path']?>h5/swoole/images/position.png" usemap="#positionMap" />
         </span>
         </p>
    </div>
</div>
<script src="<?=Yii::$app->params['static_path']?>h5/swoole/js/lib/jquery-1.10.1.min.js"></script>
<script src="<?=Yii::$app->params['static_path']?>h5/swoole/js/lib/e-smart-zoom-jquery.min.js"></script>
<!--[if lt IE 9]>
    <script src="http://libs.useso.com/js/html5shiv/3.6/html5shiv.min.js"></script>
<![endif]-->
<script>
//description：捕获url参数
function GetQueryString(name){
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  unescape(r[2]); return null;
}
$("#imageFullScreen").attr("src",GetQueryString("img"));
$(document).ready(function() {
    $('#imageFullScreen').smartZoom({'containerClass':'zoomableContainer'});
    $('#topPositionMap,#leftPositionMap,#rightPositionMap,#bottomPositionMap').bind("click", moveButtonClickHandler);
    $('#zoomInButton,#zoomOutButton').bind("click", zoomButtonClickHandler);

    function zoomButtonClickHandler(e){
        var scaleToAdd = 0.8;
        if(e.target.id == 'zoomOutButton')
            scaleToAdd = -scaleToAdd;
        $('#imageFullScreen').smartZoom('zoom', scaleToAdd);
    }
    function moveButtonClickHandler(e){
        var pixelsToMoveOnX = 0;
        var pixelsToMoveOnY = 0;

        switch(e.target.id){
            case "leftPositionMap":
                pixelsToMoveOnX = 50;
            break;
            case "rightPositionMap":
                pixelsToMoveOnX = -50;
            break;
            case "topPositionMap":
                pixelsToMoveOnY = 50;
            break;
            case "bottomPositionMap":
                pixelsToMoveOnY = -50;
            break;
        }
        $('#imageFullScreen').smartZoom('pan', pixelsToMoveOnX, pixelsToMoveOnY);
    }
});
</script>
</body>
</html>
