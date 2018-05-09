var version , isInitLazy =true;//app版本号
xyobj.getQueryString("version") != null ? version = xyobj.getQueryString("version") : version = '1.2.1';

var ws= new WebSocket(swooleIp); 
var isEnd = 1;
ws.onopen = function(e){
    initData(); //加载历史记录
}
ws.onmessage = function (e) {
    loadData(JSON.parse(e.data));//导入消息记录，加载新的消息
}

//description：加载历史记录
var initData = function() {
    var pData = {
        token: token,
        first:1
    }
    console.log(pData);
    ws.send(JSON.stringify(pData)); //获取消息记录，绑定fd
}
//description：导入消息记录，加载新的消息
var loadData = function(data){
    console.log(data);
    if(data.status != 1) {alert(data.msg); return false;}
    data = data.msg;
    var messageText,messageClass,lazyClass='';
    var timestamp = Date.parse(new Date());
    isInitLazy ? lazyClass='' : lazyClass='lazy-box'+timestamp;
    //不存在历史消息
    if(data.length > 0){
        document.getElementById('empty-chat').style.display="none";
    }
    //读取本地存储的头像和id
    for (var i = 0; i < data.length; i++) {
        if(data[i].aid > 0){
            data[i].content.substr(1,3) == 'img' ? messageClass = "chat-box ta-box img-box clearfix "+lazyClass +"" : messageClass = "chat-box ta-box clearfix";
            messageText = "<li class='time'>"+ data[i].created_at +"</li>"+
                        "<li class='"+ messageClass+"'>"+
                            "<img src='"+ data[i].img +"' alt='' class='tx'>"+
                            "<div class='con'>"+
                                "<i></i>"+
                                "<span>"+ data[i].content +"</span>"+
                            "</div>"+
                        "</li>";  
        }else{
            data[i].content.substr(1,3) == 'img' ? messageClass = "chat-box wo-box img-box clearfix "+lazyClass +"" : messageClass = "chat-box wo-box clearfix";
            messageText = "<li class='time'>"+ data[i].created_at +"</li>"+
                        "<li class='"+ messageClass+"'>"+
                           "<img src='"+ data[i].img +"' alt='' class='tx'>"+
                            "<div class='con'>"+
                                "<i></i>"+
                                "<span>"+ data[i].content +"</span>"+
                            "</div>"+
                        "</li>";
        }
        $("#messages").append(messageText);
        xyobj.scrollBom();
    }
    if(isInitLazy){
        $("img.lazy").lazyload({effect: "fadeIn"});
        isInitLazy = false;
    }else{
        $("."+lazyClass+" img.lazy").lazyload({effect: "fadeIn"});
    }
}
//description：发送文字消息
var sendMessage = function(){
    var pData = {
        content: document.getElementById('message').innerText,
        number : version,
        source: phone,
		token: token,
        system: xyobj.getQueryString("system"),
        model: xyobj.getQueryString("model")
    }
    console.log(pData);
    if($.trim(pData.content) == ''){
        alert("消息不能为空");
        return;
    }
    if(!token){
        alert('您还未登录');
        return false;
    }
    ws.send(JSON.stringify(pData)); //发送消息
    $('#message').text("");
    $(".submit-btn").hide();
    $(".imgfile,.imgfile-bg").show();
}
//description：发送图片消息
var sendImage = function(chatimg){
    var pData = {
        content: '<img class="chatImg lazy" src="'+ static_path +'h5/swoole/images/loading.gif" data-original="'+ chatimg +'" >',
        number : version,
        source: phone,
        token: token,
        system: xyobj.getQueryString("system"),
        model: xyobj.getQueryString("model")
    }
    if(pData.content == ''){
        alert("消息不能为空");
        return;
    }
    ws.send(JSON.stringify(pData));
}
//description：上传图片
var uploadImg = function(){
    var flag = true;
    $(document).on("change","#imgfile",function () {
        $(this).replaceWith("<input type='file' name='' class='imgfile' id='imgfile' onclick='uploadImg()'>");
        if(typeof this.files == "undefined"){
            return "";
        }
        var img=this.files[0];
        var type=img.type;
        var url=getObjectURL(img);
        if(type.substr(0,5) != 'image' ){
            alert("只支持图片文件");
            return;
        }
        function getObjectURL(file) {
            var url = null;
            if (window.createObjectURL != undefined) {
                url = window.createObjectURL(file)
            } else if (window.URL != undefined) {
                url = window.URL.createObjectURL(file)
            } else if (window.webkitURL != undefined) {
                url = window.webkitURL.createObjectURL(file)
            }
            return url
        };
        var reader = new FileReader();
        reader.onload = function(e) { //编码文件
            var head = e.currentTarget.result;
            $("input[name='image']").val(head);
            head = head.replace(/data:image\/(jpeg|jpg|png|gif);base64,/g,'');
            if(head!='' && flag){
                uploadImage(head);//上传图片
            }
        };
        reader.readAsDataURL(this.files[0]);
    });
    function uploadImage(uploadimg){
        if(isEnd != 1) return false;
        isEnd = 0;
        $.ajax({
            'url': apiPath+'/swoole/index',
            'type': 'POST',
            'dataType': 'json',
            'crossDomain': true,
            'data': {
                'action': 'uploadImg',
                'from': phone,
                'token': token,
                'imgFile': uploadimg
            },
            beforeSend:function(XMLHttpRequest){
                var imgload ="<li class='chat-box wo-box img-box clearfix img-load'>"+
                                "<img src='"+ xyobj.getCookie("yjyuimg") +"' alt='' class='tx'>"+
                                "<div class='con'>"+
                                    "<span><img src='"+ static_path +"h5/swoole/images/loading.gif'></span>"+
                                "</div>"+
                           "</li>";
                $("#messages").append(imgload);
                xyobj.scrollBom();
            },
            success: function(d){
                if(d.status == 1){
                    flag = false;
                    var chatimg = 'http://oss.yjyapp.com/'+d.msg;
                    sendImage(chatimg);
                }else{
                    alert(d.msg);
                }
            },
            complete:function(XMLHttpRequest,textStatus){
                isEnd = 1;
                $(".img-load").remove();
            },
            error: function(d){
                isEnd = 1;
                $(".img-load").remove();
                alert("request error!");
            }
        });
    }
}
//description：android发送
$("#message").on("input",function (e){
    var conval = $(this).text();
    $(".submit-btn").show();
    $(".imgfile,.imgfile-bg").hide();
    // if (xyobj.browser().android){
    //     $(".submit-btn").show();
    // }
    if(conval == ''){
        $(".submit-btn").hide();
        $(".imgfile,.imgfile-bg").show();
    }
});
// $("#message").on("focus",function (e){
//     var winh= $(window).scrollTop();
//     var self = this;
//     setTimeout(function(){
//         $('body,html').animate({'scrollTop':winh+1000},0);
//         self.scrollIntoView(true);
//         self.scrollIntoViewIfNeeded();
//     },200);
// });
$('body,html').animate({'scrollTop':1000},0);