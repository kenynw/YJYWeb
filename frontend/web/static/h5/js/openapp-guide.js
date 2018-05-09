(function () {
    var openapp = window.openapp = {
        //description：页面字体rem初始化
        init_size:function () {
            //初始化html字体大小
            var rem_ReSize = function(){
                var w = $(window).width();
                if (w > 640) {
                    w = 640;
                };
                //html元素字体大小 = document根节点(html)宽度 * 100 / 设计图宽度
                $('html').css('font-size', 100 / 640 * w + 'px');
            };
            rem_ReSize();
            //当窗体大小变化时，html字体大小随着变化
            window.onresize = function(){
                rem_ReSize();
            };
            window.onload = function () {
                rem_ReSize();
            };
        },
        //移动终端浏览器版本信息
        browser:function(){
            var version= function(){
                var o = navigator.userAgent,
                    apple= /iPhone|iPad/.test(o),
                    ioswv= apple && !/Version.+Safari/.test(o);
                return {
                    mobile: !!o.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
                    ios: /iPhone|iPad/.test(o), //ios终端
                    android: /Android/.test(o), //android终端
                    wechat: /MicroMessenger/.test(o), //是否为微信内置浏览器
                    iOSWebView: ioswv,//ios终端webview
                    iOSSafari: apple && !ioswv,//ios终端safari浏览器
                    iOS8AndMinor: apple && Number(o.match(/OS ((\d+_?){2,3})\s/)[1].split("_")[0]) <= 8,
                    weibo:/Weibo/.test(o),//微博
                    Douban:/douban/.test(o),//豆瓣
                    QQ:/\sQQ\//.test(o)//QQ
                };
            };
            return version();
        },
        schemeOpen:function(scheme,unopenfun){
            var ifr = document.createElement('iframe');
            ifr.src = scheme;
            ifr.style.display = 'none';
            if(this.browser().iOS8AndMinor){
                document.body.appendChild(ifr);
            }else{
                window.location=scheme;
            }
            var aa = Date.now();
            setTimeout(function(){
               var cc = Date.now();
               if(cc - aa <(1500 + 200)){
                    unopenfun();
               }else{
                   document.body.removeChild(ifr);
               }
            },1500)
        },
        //description：捕获url参数
        GetQueryString: function (name){
             var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
             var r = window.location.search.substr(1).match(reg);
             if(r!=null)return  unescape(r[2]); return null;
        },
        checkOpen:function(){
            var self= this,
                type=self.GetQueryString("unltype"),
                relation=self.GetQueryString("unlrelation"),
                webapp = self.browser().wechat || self.browser().weibo || self.browser().QQ ;
            if(webapp){
                self.browser().ios ? document.getElementById("js_txt").innerText="点击右上角用Safari打开" : document.getElementById("js_txt").innerText="点击右上角用浏览器打开";
            }
            if(self.browser().iOSSafari){
                document.getElementById("js_guide").style.display="none";
                document.getElementById("js_downled").style.display="block";
                document.getElementById("js_downled").onclick = function(){
                    self.schemeOpen("com.miguan.yanjiuyuan://openurl?unltype="+type+"&unlrelation="+relation+"",function(){
                        window.location="https://itunes.apple.com/cn/app/%E9%A2%9C%E7%A9%B6%E9%99%A2-%E7%A7%91%E5%AD%A6%E6%8A%A4%E8%82%A4%E7%A5%9E%E5%99%A8/id1216109447?mt=8";
                    });
                }
            }
            if(!webapp && self.browser().android){
                self.schemeOpen("yjyappscheme://yjy.app/openwith?unltype="+type+"&unlrelation="+relation+"",function(){
                    //document.getElementById("js_ptit").innerHTML="";
                    document.getElementById("js_guide").style.display="none";
                });
            }
        },
        init:function(){
            this.init_size();
            this.checkOpen();
        }
    }
})();

openapp.init();
