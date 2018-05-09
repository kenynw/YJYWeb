var slidebar = {
    slide_toggle:function(){
        var num,
            $pro_li = $(".recommend-pro-right .pro-list li"),//推荐产品
            $art_li = $(".recommend-art-right .pro-list li"),//推荐文章
            $table_down = $(".btn-up-down"),//展开成份表
            $cf_table = $(".cf-table"),//成分表
            $com_textarea = $(".textarea-box textarea"),//评论框
            $txt_num = $(".textarea-box .num i"),//评论框
            $com_btn = $(".comment-btn"),//评论按钮
            $span_tab = $(".common-pro-box .line span"),//同品牌/分类推荐产品 tab
            $tab_con = $(".common-pro-box .pro-box"),//同品牌/分类推荐产品 内容
            $code_close = $(".icon-pro-com-close"),//评论微信登录二维码 close
            $code_shade = $(".code-shade");//评论微信登录二维码
        //右边栏 推荐产品列表显示隐藏
        $pro_li.eq(0).find(".pro-name").hide();
        $pro_li.eq(0).find(".pro-show").show();
        $pro_li.hover(function(){
            var self = $(this);
            self.find(".pro-name").hide();
            self.siblings("li").find(".pro-name").show();
            self.find(".pro-show").show();
            self.siblings("li").find(".pro-show").hide();
        });
        //右边栏 推荐文章列表显示隐藏
        $art_li.eq(0).find(".pro-name").hide();
        $art_li.eq(0).find(".art-show").show();
        $art_li.hover(function(){
            var self = $(this);
            self.find(".pro-name").hide();
            self.siblings("li").find(".pro-name").show();
            self.find(".art-show").show();
            self.siblings("li").find(".art-show").hide();
        });
        //产品详情页 成分表展开收起
        $table_down.on("click",function(){
            var self = $(this);
            if(self.hasClass("close")){
                $cf_table.css("max-height","579px");
                self.removeClass("close").find("span").html("展开成分表");
                self.find("i").attr("class","icon-pro-down");
            }else{
                $cf_table.css("max-height","none");
                self.addClass("close").find("span").html("收起成分表");
                self.find("i").attr("class","icon-pro-up");
            }
        });
        //产品详情页 评论
        $com_textarea.focus(function(){
            $(this).css("border","1px solid #32dac3");
            $com_btn.show();
        }).blur(function(){
            $(this).css("border","1px solid #e5e5e5");
            // $com_btn.hide();
        });
        //产品详情页 同品牌/分类推荐产品 选项卡
        $span_tab.each(function(i) {
            var self = $(this);
            self.on("click", function() {
                if (!self.hasClass("cur")) {
                    self.addClass("cur").siblings("span").removeClass("cur");
                }
                $tab_con.eq(i).show().siblings(".pro-box").hide();
            });
        });
        //产品详情页 二维码遮罩显示隐藏
        $com_btn.on("click",function(){
            $code_shade.show();
        });
        $code_close.on("click",function(){
            $code_shade.hide();
        });
        // 产品详情页 评论字数控制
        $com_textarea.keyup(function(){
            num =$com_textarea.val().length;
            if(num<200){
                $txt_num.text(200-num);
            }else{
                $txt_num.text(200);
            }
        });
    }
}
slidebar.slide_toggle();
