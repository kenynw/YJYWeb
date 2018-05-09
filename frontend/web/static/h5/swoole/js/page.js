var apiPath = apiPathUrl,
    token, //发送者TOKEN
    phone,//移动终端
    uimg,//用户头像
    uid;//用户id
(function(){
    'use strict';
    var xyobj = window.xyobj = {
        //description：页面字体rem初始化
        initSize:function () {
            //初始化html字体大小
            var remReSize = function(){
                var w = $(window).width();
                if (w > 640) {
                    w = 640;
                };
                //html元素字体大小 = document根节点(html)宽度 * 100 / 设计图宽度
                $('html').css('font-size', 100 / 750 * w + 'px');
            };
            remReSize();
            //当窗体大小变化时，html字体大小随着变化
            window.onresize = function(){
                remReSize();
            };
            window.onload = function () {
                remReSize();
            };
        },
        //description：图片放大
        imgScale:function(){
            $(".chatroom-box").on("click",".chatImg",function(){
                var urlimg = $(this).attr("src");
                $(".imgZoom img").attr("src",urlimg);
                $(".imgZoom").show();
                // var imgw = $(".imgZoom img").width(),
                //     imgh = $(".imgZoom img").height();
                // $(".imgZoom img").css({
                //     'margin-left': -imgw/2,
                //     'margin-top': -imgh/2
                // });
            });
            $(".imgZoom").on("click", function (e) {
                $(".imgZoom").hide();
            });
        },
        //description：始终显示最新聊天记录
        scrollBom:function(){
            window.location.hash = "jdw";
            window.location = window.location;
        },
        //description：捕获url参数
        getQueryString: function (name){
             var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
             var r = window.location.search.substr(1).match(reg);
             if(r!=null)return  unescape(r[2]); return null;
        },
        //description：设置cookie
        setCookie: function(name,value){
            if(window.localStorage){
                localStorage.removeItem(name);
                localStorage.setItem(name,value);
            } else {
                var Days = 30;
                var exp = new Date();
                exp.setTime(exp.getTime() + Days*24*60*60*1000);
                document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
            };
            return false;
        },
        //description：读取cookie
        getCookie: function(name) {
            if(window.localStorage){
                var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
                arr = localStorage.getItem(name);
                return arr;
            } else {
                var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
                if(arr=document.cookie.match(reg)){
                    return unescape(arr[2]);
                }else{
                    return null;
                }
            };
        },
        //description：移动终端判断
        browser:function(){
            var o = navigator.userAgent,
                apple= /iPhone|iPad/.test(o),
                ioswv= apple && !/Version.+Safari/.test(o);
            return {
                mobile: !!o.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
                ios: /iPhone|iPad/.test(o), //ios终端
                android: /Android/.test(o) //android终端
            };
        },
        //description：获取token
        getToken:function(){
            var self = this;
            token = self.getQueryString("token");
            if(token!=''){
                self.initUserInfo(token);
            }
        },
        //description：初始化获取用户信息
        initUserInfo : function(token){
            var self = this;
            self.browser().ios ? phone = 'ios' : phone = 'android';
            var data_ajax = {
                'url': apiPath+'/swoole/index',
                'type': 'GET',
                'dataType': 'jsonp',
                'data': {
                    'action': 'userInfo',
                    'from': phone,
                    'token': token
                },
                success: function(d){
                    if(d.status == 1){
                        uimg = d.msg.img;//用户头像
                        uid = d.msg.id;//用户id
                        self.setCookie("yjyuimg",uimg);
                        self.setCookie("yjyuid",uid);
                    }else{
                        alert(d.msg);
                    }
                },
                error: function(d){
                    alert("request error!");
                }
            };
            $.ajax(data_ajax);
        },
        init:function(){
            this.initSize();
            this.scrollBom();
            this.getToken();
            this.imgScale();
        }
    }
})();
xyobj.init();
