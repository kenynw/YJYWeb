<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>颜究院接口文档</title>
    </head>
    <style type="text/css">
        html, body { padding:0; margin:0; }
        body { font-size:14px; width:1000px; }
        body, h1, h2, h3 { font-family:"微软雅黑","Lucida Grande","Lucida Sans Unicode",Calibri,Arial,Helvetica,Sans,FreeSans,Jamrul,Garuda,Kalimati; }
        b { color:#0000cc; }

        #menu { background:#EAEEF3; width:200px; border-right:1px solid #C6C9CE; float:left; height:100%; position:fixed; top:0; bottom:0; left:0; right:0;overflow: auto; }
        #menu_title { padding:5px 20px; margin:0; text-align:right; font-size:24px; color:#576780; }
        #menu ul, #menu li { list-style:none; margin:0; padding:0; }
        #menu li a { border:1px solid #EAEEF3; border-right:none; border-left:none; }
        #menu a { font-size:14px; display:block; padding:5px 24px; text-decoration:none; text-align:right; color:#426DC9; }
        #menu .current a, #menu li a:hover { background:#BBCEE9; color:#000; border-color:#8FAAD9; }

        #content { margin-left:220px; padding-bottom:24px; margin-right:24px; }
        #content h3 { font-size:24px; color:#576780; border-bottom:1px solid #eee; padding-bottom:8px; margin-top:24px; }
        #content ul, #content li { margin:0; padding:0; list-style:none; color:#ccc; }
        #content li span { color:#000; }
        #content li { line-height:24px; list-style-type:disc; margin-left:30px; }
        #content p { line-height:24px; }
        #content .subp { margin-left:30px; }

        #footer { text-align:center; font-size:12px; color:#888; border-top:solid 1px #eee; padding-top:10px; margin-top:24px; }
    </style>
    <script type="text/javascript">
        var currentItem = '001';
    </script>
    <body>
        <div id="menu">
            <h3 id="menu_title">目录</h3>
            <ul>
                <li id="A001" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#001">首页接口</a>
                </li>
                <li id="A002" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#002">热门接口</a>
                </li>
                <li id="A017" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#017">文章详情</a>
                </li>
                <li id="A003" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#003">产品详情接口</a>
                </li>
                <li id="A004" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#004">评论列表接口</a>
                </li>
                <li id="A005" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#005">评论点赞接口</a>
                </li>
                <li id="A006" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#006">评论接口</a>
                </li>
                <li id="A007" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#007">批号查询列表接口</a>
                </li>
                <li id="A008" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#008">批号查询接口</a>
                </li>
                <li id="A009" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#009">保质期提醒接口</a>
                </li>
                <li id="A010" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#010">用户资料接口</a>
                </li>
                <li id="A011" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#011">用户反馈接口</a>
                </li>
                <li id="A012" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#012">版本更新接口</a>
                </li>
                <li id="A013" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#013">用户手机绑定</a>
                </li>
                <li id="A014" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#014">用户长草</a>
                </li>
                <li id="A018" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#018">用户肤质接口</a>
                </li>
                <li id="A015" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#015">肤质提交接口</a>
                </li>
                <li id="A019" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#019">肤质推荐接口</a>
                </li>
                <li id="A020" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#020">推荐列表接口</a>
                </li>
                <li id="A021" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#021">未读消息数接口</a>
                </li>
                <li id="A022" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#022">设为已读接口</a>
                </li>
                <li id="A016" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#016">用户颜值记录接口</a>
                </li>
                <li id="A023" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#023">分享记录接口</a>
                </li>
                <li id="A024" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#024">功课记录接口</a>
                </li>
                <li id="A025" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#025">轮播记录接口</a>
                </li>
                <li id="U1001" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1001">发送短信接口</a>
                </li>
                <li id="U1002" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1002">注册接口</a>
                </li>
                <li id="U1003" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1003">登录接口</a>
                </li>
                <li id="U1004" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1004">第三方登录接口</a>
                </li>
                <li id="U1005" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1005">添加收藏-长草</a>
                </li>
                <li id="U1006" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1006">用户点评</a>
                </li>
                <li id="U1007" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1007">用户的消息</a>
                </li>
                <li id="U1008" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1008">用户重置密码</a>
                </li>
                <li id="U1009" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1009">用户资料修改</a>
                </li>
                <li id="U1010" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1010">用户在用列表</a>
                </li>
                <li id="U1011" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1010">操作用户在用信息</a>
                </li>
                <li id="U1012" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1012">用户收藏</a>
                </li>
                <li id="U1013" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1013">大家都在搜</a>
                </li>
                <li id="U1014" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1014">搜索联想接口</a>
                </li>
                <li id="U1015" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';">
                    <a href="#1015">搜索结果接口</a>
                </li>
                <li id="i97" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';"><a href="#097">推送说明</a></li>
                <li id="i98" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';"><a href="#098">公共参数</a></li>
                <li id="i99" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';"><a href="#y">公共加密</a></li>
                <li id="i100" onclick="document.getElementById(currentItem).className = ''; currentItem = this.id; this.className = 'current';"><a href="#z">公共返回值</a></li>
            </ul>
        </div>
        <div id="content">
            <h4 id="001">BASEPATH(http://api.yjyapp.com/api/index)</h4>
            <!-- ---------------------------------------------首页接口--------------------------------------------------- -->
            <h3 id="001">首页接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b> － 固定值index</span></li>
                <li><span><b>user_id(int)</b>   － 用户ID，未登录可为空</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>num(int)</b>       － 产品总数</span></li>
                <li><span><b>banner(array)</b>  － 轮播参数</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>        － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>type(int)</b>      － 1:H5,2产品详情,3:文章详情</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>title(string)</b>  － 标题</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>img(string)</b>    － 图片地址</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>url(string)</b>    － 跳转地址,type为1是有效</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>relation_id(int)</b>－ 关联ID，如果type为2，3则为对应ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>comment_num(int)</b>   － 评论数</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>likeUrl(string)</b>    － 文章链接</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>isGras(int)</b>        － 是否收藏或长草，1为是，0为否</span></li>
                <li><span><b>category(array)</b> － 栏目参数</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>        － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>cate_name(string)</b>  － 栏目名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>cate_img(string)</b>   － 栏目图</span></li>
                <li><span><b>article(array)</b>  － 文章列表</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>title(string)</b>      － 标题</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span><b>article_img(string)</b>－ 配图</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>like_num(int)</b>      － 点赞数</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>comment_num(int)</b>   － 评论数</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>isGras(int)</b>        － 是否收藏或长草，1为是，0为否</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>likeUrl(string)</b>    － 文章链接</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>created_at(string)</b> － 创建时间</span></li>
                <li><span><b>pageTotal(int)</b>     － 总数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页条数</span></li>
            <!-- ---------------------------------------------热门文章接口--------------------------------------------------- -->
            <h3 id="002">热门文章接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值articleList</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID，未登录可为空</span></li>
                <li><span><b>page(int)</b>          － 当前页数</span></li>
                <li><span><b>pageSize(string)</b>   － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>            － ID</span></li>
                <li><span><b>title(string)</b>      － 标题</span></li>
                <li><span><b>article_img(string)</b>－ 配图</span></li>
                <li><span><b>like_num(int)</b>      － 点赞数</span></li>
                <li><span><b>comment_num(int)</b>   － 评论数</span></li>
                <li><span><b>likeUrl(string)</b>    － 文章链接</span></li>
                <li><span><b>isGras(int)</b>        － 是否收藏或长草，1为是，0为否</span></li>
                <li><span><b>created_at(string)</b> － 创建时间</span></li>
                <li><span><b>pageTotal(int)</b>     － 总数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页条数</span></li>
            <!-- ---------------------------------------------文章详情接口--------------------------------------------------- -->
            <h3 id="017">文章详情接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值articleInfo</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID，未登录可为空</span></li>
                <li><span><b>id(int)</b>            － 文章ID</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>            － ID</span></li>
                <li><span><b>title(string)</b>      － 标题</span></li>
                <li><span><b>article_img(string)</b>－ 配图</span></li>
                <li><span><b>like_num(int)</b>      － 点赞数</span></li>
                <li><span><b>comment_num(int)</b>   － 评论数</span></li>
                <li><span><b>likeUrl(string)</b>    － 文章链接</span></li>
                <li><span><b>isGras(int)</b>        － 是否收藏或长草，1为是，0为否</span></li>
                <li><span><b>created_at(string)</b> － 创建时间</span></li>
                <li><span><b>commentList(array)</b> － 评论</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;id(int)</b>            － ID</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;comment(string)</b>    － 评论内容</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;like_num(int)</b>      － 点赞数</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;username(string)</b>   － 用户名</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;img(string)</b>        － 用户头像</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;birth_year(int)</b>    － 用户年龄</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;skin(string)</b>       － 用户肤质</span></li>
                <li><span><b>&nbsp;&nbsp;&nbsp;&nbsp;isLike(int)</b>        － 是否已点赞</span></li>
            <!-- ---------------------------------------------产品详情接口--------------------------------------------------- -->
            <h3 id="003">产品详情接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值productInfo</span></li>
                <li><span><b>id(int)</b>            － 产品ID</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID，未登录可为空</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>                － ID</span></li>
                <li><span><b>cate_id(int)</b>           － 栏目ID</span></li>
                <li><span><b>product_name(string)</b>   － 产品名</span></li>
                <li><span><b>price(float)</b>           － 价格</span></li>
                <li><span><b>form(string)</b>           － 规格</span></li>
                <li><span><b>alias(string)</b>          － 英文名</span></li>
                <li><span><b>star(int)</b>              － 星级</span></li>
                <li><span><b>standard_number(string)</b>－ 备案号</span></li>
                <li><span><b>product_country(string)</b>－ 生产国</span></li>
                <li><span><b>product_date(int)</b>      － 批准日期</span></li>
                <li><span><b>remark(string)</b>         － 别名</span></li>
                <li><span><b>product_img(string)</b>    － 产品图</span></li>
                <li><span><b>product_company(string)</b>－ 生产厂家</span></li>
                <li><span><b>brand(string)</b>          － 品牌</span></li>
                <li><span><b>en_product_company(string)</b>－ 生产厂家（英文名）</span></li>
                <li><span><b>isGras(int)</b> － 1已长草，0未长草</span></li>
                <li><span><b>Praise(int)</b>        － 好评数</span></li>
                <li><span><b>middle(int)</b>        － 中评数</span></li>
                <li><span><b>bad(int)</b>           － 差评数</span></li>
                <li><span><b>total(int)</b>         － 总数</span></li>
                <li><span><b>likeUrl(string)</b>    － 文章链接</span></li>
                <li><span><b>effectNum(int)</b>     － 功效成份数</span></li>
                <li><span><b>componentList(array)</b> － 成分</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(string)</b>       － 成分名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>risk_grade(int)</b>    － 风险等级</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>is_active(int)</b>     － 活性成分 1-是，0-否</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>is_pox(int)</b>        － 致痘 1-是，0-否</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>component_action(string)</b>   － 使用目的</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>description(string)</b>   － 简介</span></li>
                <li><span><b>effect(array)</b>     － 功效成份【其中有几种】</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(string)</b>      － 功效名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(array)</b>         － 成份ID</span></li>
                <li><span><b>security(array)</b>   － 安全成份【其中有几种】</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(string)</b>      － 安全成份名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(array)</b>         － 成份ID</span></li>
                <li><span><b>recommend(array)</b>  － 推荐成份</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>         － 成份ID</span></li>
                <li><span><b>notRecommend(array)</b>－ 不推荐成份</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>         － 成份ID</span></li>
                <li><span><b>buy(array)</b>   － 商城链接</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>jd(string)</b>         － 京东</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>taobao(string)</b>     － 淘宝</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>amazon(int)</b>        － 亚马逊</span></li>
            <!-- ---------------------------------------------评论列表接口--------------------------------------------------- -->
            <h3 id="004">评论列表接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值commentList</span></li>
                <li><span><b>id(int)</b>            － 对应ID</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID，未登录可为空</span></li>
                <li><span><b>type(int)</b>          － 类型 1-产品，2-文章</span></li>
                <li><span><b>orderBy(string)</b>    － 排序方式-默认default综合排序，skin 肤质排序</span></li>
                <li><span><b>condition(string)</b>  － 筛选星级,目前有'Praise'好评,'middle'中评,'bad'差评</span></li>
                <li><span><b>page(int)</b>          － 当前页数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>            － ID</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>comment(string)</b>    － 评论内容</span></li>
                <li><span><b>like_num(int)</b>      － 点赞数</span></li>
                <li><span><b>username(string)</b>   － 用户名</span></li>
                <li><span><b>img(string)</b>        － 用户头像</span></li>
                <li><span><b>birth_year(int)</b>    － 用户年龄</span></li>
                <li><span><b>skin(string)</b>       － 用户肤质</span></li>
                <li><span><b>isLike(int)</b>        － 是否已点赞</span></li>
                <li><span><b>pageTotal(int)</b>     － 总数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页条数</span></li>
            <!-- ---------------------------------------------评论点赞接口--------------------------------------------------- -->
            <h3 id="005">评论点赞-取赞接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值addCommentLike</span></li>
                <li><span><b>commentId(int)</b>     － 评论ID</span></li>
                <li><span><b>user_id(string)</b>    － 用户ID</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:非1,msg:错误信息}</span></li>
                    <li><span>成功返回{status:1,msg:提示信息}</span></li>
                </ul>
            <!-- ---------------------------------------------评论接口--------------------------------------------------- -->
            <h3 id="006">评论接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值addComment</span></li>
                <li><span><b>user_id(string)</b>    － 用户ID</span></li>
                <li><span><b>type(int)</b>          － 1为产品，2为文章</span></li>
                <li><span><b>id(int)</b>            － 对应ID</span></li>
                <li><span><b>star(int)</b>          － 星级(文章可不传)</span></li>
                <li><span><b>comment(string)</b>    － 评论内容</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:非1,msg:错误信息}</span></li>
                    <li><span>成功返回{status:1,msg:提示信息,commentId: 生成的评论ID}</span></li>
                </ul>
            <!-- ---------------------------------------------批号查询列表接口--------------------------------------------------- -->
            <h3 id="007">批号品牌列表接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值brandList</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>letterArr(array)</b>   － 字母集合</span></li>
                <li><span><b>hotCosmetics(array)</b>－ 热门品牌</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(string)</b>       － 化妆品牌名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>letter(string)</b>     － 字母开头</span></li>
                <li><span><b>otherCosmetics(array)</b>－ 其他品牌</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(string)</b>       － 化妆品牌名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>letter(string)</b>     － 字母开头</span></li>
            <!-- ---------------------------------------------批号查询接口--------------------------------------------------- -->
            <h3 id="008">批号查询接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值queryCosmetics</span></li>
                <li><span><b>id(int)</b>            － 品牌ID</span></li>
                <li><span><b>number(string)</b>     － 批号</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:-1,msg:错误信息}</span></li>
                    <li><span>成功返回{status:1,msg:批号数据}</span></li>
                </ul>
            <p>附加说明：</p>
                <li><span><b>startDay(string)</b>   － 生产日期</span></li>
                <li><span><b>endDay(string)</b>     － 过期日期</span></li>
            <!-- ---------------------------------------------保质期提醒接口--------------------------------------------------- -->
            <h3 id="009">保质期提醒接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值addRemind</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>brand_id(int)</b>      － 品牌ID</span></li>
                <li><span><b>brand_name(string)</b> － 品牌名</span></li>
                <li><span><b>product(string)</b>    － 产品名</span></li>
                <li><span><b>is_seal(int)</b>       － 是否开封,0未开封，1已开封</span></li>
                <li><span><b>seal_time(int)</b>     － 开封时间</span></li>
                <li><span><b>quality_time(int)</b>  － 开封后保质期  -- 保质期，单位为月</span></li>
                <li><span><b>overdue_time(int)</b>  － 过期时间(未开封为生产日期+3年，已开封为开封时间加保质期时间。)</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:操作失败}</span></li>
                    <li><span>成功返回{status:1,msg:操作成功}</span></li>
                </ul>
            <!-- ---------------------------------------------用户资料接口--------------------------------------------------- -->
            <h3 id="010">用户资料接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值userInfo</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>            － 用户ID</span></li>
                <li><span><b>username(string)</b>   － 用户名</span></li>
                <li><span><b>mobile(string)</b>     － 手机</span></li>
                <li><span><b>img(string)</b>        － 头像</span></li>
                <li><span><b>img_state(int)</b>     － 头像状态 0正常，1禁用</span></li>
                <li><span><b>sex(int)</b>           － 姓别 0女，1男</span></li>
                <li><span><b>birth_year(int)</b>    － 出生年</span></li>
                <li><span><b>birth_month(int)</b>   － 出生月</span></li>
                <li><span><b>birth_day(int)</b>     － 出生日</span></li>
                <li><span><b>province(string)</b>   － 所在省</span></li>
                <li><span><b>city(string)</b>       － 所在市</span></li>
                <li><span><b>status(int)</b>        － 账号状态，1为正常，2为禁言，3为封号</span></li>
                <li><span><b>rank_points(int)</b>   － 颜值</span></li>
                <li><span><b>dry(int)</b>           － 干性值</span></li>
                <li><span><b>tolerance(int)</b>     － 敏感值</span></li>
                <li><span><b>pigment(int)</b>       － 色素值</span></li>
                <li><span><b>compact(int)</b>       － 紧致值</span></li>
                <li><span><b>skin_name(string)</b>  － 肤质,如DSNP</span></li>
            <!-- ---------------------------------------------用户反馈接口--------------------------------------------------- -->
            <h3 id="011">用户反馈接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值userFeedback</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>username(string)</b>   － 用户名</span></li>
                <li><span><b>content(int)</b>       － 反馈内容</span></li>
                <li><span><b>telphone(int)</b>      － 手机号【非必传参数】</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:操作失败}</span></li>
                    <li><span>成功返回{status:1,msg:操作成功}</span></li>
                </ul>
            <!-- ---------------------------------------------版本更新接口--------------------------------------------------- -->
            <h3 id="012">版本更新接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值versionUp</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>            － ID</span></li>
                <li><span><b>type(int)</b>          － 1-android 2-ios</span></li>
                <li><span><b>content(string)</b>    － 更新内容</span></li>
                <li><span><b>number(string)</b>     － 版本号</span></li>
                <li><span><b>downloadUrl(string)</b>－ 下载地址</span></li>
            <!-- ---------------------------------------------用户手机绑定接口--------------------------------------------------- -->
            <h3 id="013">用户手机绑定接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值mobileBind</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>mobile(string)</b>     － 手机号</span></li>
                <li><span><b>captcha(int)</b>       － 验证码</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:操作失败}</span></li>
                    <li><span>成功返回{status:1,msg:操作成功}</span></li>
                </ul>
            <!-- ---------------------------------------------用户长草接口--------------------------------------------------- -->
            <h3 id="014">用户长草接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值userGrass</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>cate_id(string)</b>    － 栏目ID</span></li>
                <li><span><b>effect(int)</b>        － 功效</span></li>
                <li><span><b>page(int)</b>          － 当前页数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>data(array)</b>        － 长草数据</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_name(int)</b>  － 产品名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_img(string)</b>－ 产品图</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>pageTotal(int)</b>     － 总数</span></li>
                <li><span><b>categroy(array)</b>    － 分类数据</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>cate_name(string)</b>  － 分类名</span></li>  
                <li><span><b>effects(array)</b>     － 功效数据</span></li>  
                <li><span><b>pageTotal(int)</b>     － 总条数</span></li>
                <li><span><b>pageSize(int)</b>      － 当前页数</span></li>
            <!-- ---------------------------------------------用户肤质接口--------------------------------------------------- -->
            <h3 id="018">用户肤质接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值userSkin</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>status(int)</b>        － 状态，1成功或0失败</span></li>
                <li><span><b>desc(array)</b>        － 分类数据</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(string)</b>       － 肤质中文</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>letter(string)</b>     － 肤质简写</span></li>
                <li><span><b>explain(string)</b>    － 描述</span></li>
                <li><span><b>features(string)</b>   － 特征</span></li>  
                <li><span><b>elements(string)</b>   － 保养要素</span></li>  
            <!-- ---------------------------------------------肤质提交接口--------------------------------------------------- -->
            <h3 id="015">肤质提交接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值saveSkin</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>type(string)</b>       － dry,tolerance,pigment,compact四种类型</span></li>
                <li><span><b>value(int)</b>         － 对应值</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>status(int)</b>        － 状态，1成功或0失败</span></li>
                <li><span><b>msg(string)</b>        － 信息</span></li>
            <!---------------------------------------------用户肤质提交推荐接口--------------------------------------------------- -->
            <h3 id="019">肤质推荐接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值getSkinRecommend</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>status(int)</b>        － 状态，1成功或0失败</span></li>
                <li><span><b>categoryList(array)</b>－ 栏目列表</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>    － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>name(int)</b>  － 栏目名</span></li>
                <li><span><b>skinProduct(string)</b>－ 推荐产品</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_name(int)</b>  － 产品名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_img(string)</b>－ 产品图</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>cate_id(int)</b>       － 分类ID</span></li>
                <li><span><b>skinArticle(array)</b>  － 推荐文章</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>        － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>title(string)</b>  － 标题</span></li>  
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>article_img(string)</b> － 文章配图</span></li>  
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>like_num(int)</b>     － 点赞数</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>created_at(int)</b>   － 发布时间</span></li>
            <!---------------------------------------------肤质推荐列表接口--------------------------------------------------- -->
            <h3 id="020">肤质推荐列表接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值getSkinRecommendList</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>cate_id(string)</b>    － 栏目ID</span></li>
                <li><span><b>page(int)</b>          － 当前页数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>status(int)</b>        － 状态，1成功或0失败</span></li>
                <li><span><b>msg(array)</b>         － 推荐产品</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>            － ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_name(int)</b>  － 产品名</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_img(string)</b>－ 产品图</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>star(int)</b>          － 星级</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>form(string)</b>       － 规格</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>price(float)</b>       － 价格</span></li>
            <!-- ---------------------------------------------用户颜值记录---------------------------------------------------- -->
            <h3 id="016">用户颜值接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值faceList</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>page(int)</b>          － 当前页数</span></li>
                <li><span><b>pageSize(int)</b>      － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>money(int)</b>         － 数量</span></li>
                <li><span><b>content(string)</b>    － 内容</span></li>
                <li><span><b>created_at(int)</b>    － 时间</span></li>
                <li><span><b>pageTotal(int)</b>     － 总条数</span></li>
                <li><span><b>pageSize(int)</b>      － 当前页数</span></li>
            <!---------------------------------------------消息未读数 ----------------------------------------------------- -->
            <h3 id="021">消息未读数</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值noticeUnread</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>overdueNum(int)</b>    － 过期产品数</span></li>
                <li><span><b>unReadNum(int)</b>     － 未读消息数</span></li>
                <li><span><b>isComplete(int)</b>    － 是否完成肤质测试(0未完成 1已完成)</span></li>
            <!---------------------------------------------设为已读接口 ----------------------------------------------------- -->
            <h3 id="022">设为已读接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值readNotice</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>id(int)</b>            － 消息ID</span></li>
                <li><span><b>type(string)</b>       － 分别为system-活动通知，notice-过期通知，pms-点赞通知</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:处理失败}</span></li>
                    <li><span>成功返回{status:1,msg:处理成功}</span></li>
                </ul>
            <!---------------------------------------------分享记录接口 ----------------------------------------------------- -->
            <h3 id="023">分享记录接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值addShare</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>id(int)</b>            － 对应ID</span></li>
                <li><span><b>type(string)</b>       － 1为产品，2为文章</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:处理失败}</span></li>
                    <li><span>成功返回{status:1,msg:处理成功}</span></li>
                </ul>
            <!---------------------------------------------功课记录接口 ----------------------------------------------------- -->
            <h3 id="024">功课记录接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值addLessons</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:处理失败}</span></li>
                    <li><span>成功返回{status:1,msg:处理成功}</span></li>
                </ul>
            <!---------------------------------------------轮播记录接口 ----------------------------------------------------- -->
            <h3 id="025">轮播记录接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值bannerLog</span></li>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>id(int)</b>            － 轮播ID</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:处理失败}</span></li>
                    <li><span>成功返回{status:1,msg:处理成功}</span></li>
                </ul>
            <!-- ---------------------------------------------短信发送接口--------------------------------------------------- -->
            <h3 id="1001">短信发送接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b> － 固定值message</span></li>
                <li><span><b>mobile(string)</b> － 电话号码</span></li>
                <li><span><b>type(int)</b>      － 0注册 1找回 2登录 3登录注册 4为绑定</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:0,msg:处理失败}</span></li>
                    <li><span>成功返回{status:1,msg:处理成功}</span></li>
                </ul>
            
            <!-- ---------------------------------------------注册接口--------------------------------------------------- -->
            <h3 id="1002">注册接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值register</span></li>
                <li><span><b>mobile(int)</b>        － 手机号码</span></li>
                <li><span><b>captcha(int)</b>       － 验证码</span></li>
                <li><span><b>password(string)</b>   － 密码</span></li>
            </ul>
            <p>调用结果：</p>
                <ul>
                    <li><span>失败返回{status:非1,msg:错误信息}</span></li>
                    <li><span>成功返回{status:1,msg:用户id}</span></li>
                </ul>
            <!-- ---------------------------------------------登录接口--------------------------------------------------- -->
            <h3 id="1003">登录接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值login</span></li>
                <li><span><b>mobile(string)</b>     － 验证码</span></li>
                <li><span><b>password(string)</b>   － 密码</span></li>
                <li><span><b>type(int)</b>          － 登陆方式（1密码登录）</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>userId(int)</b>            － 用户ID</span></li>
                <li><span><b>usernName(string)</b>      － 用户昵称</span></li>
                <li><span><b>userImg(string)</b>        － 用户头像</span></li>
            <!-- ---------------------------------------------第三方登录接口--------------------------------------------------- -->
            <h3 id="1004">第三方登录接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值thirdLogin</span></li>
                <li><span><b>openid(string)</b>     － 微信openid</span></li>
                <li><span><b>unionid(string)</b>    － 微信unionid</span></li>
                <li><span><b>nickname(string)</b>   － 微信昵称</span></li>
                <li><span><b>sex(int)</b>           － 性别</span></li>
                <li><span><b>province(string)</b>   － 省</span></li>
                <li><span><b>city(string)</b>       － 市</span></li>
                <li><span><b>headimgurl(string)</b> － 微信头像地址</span></li>
                <li><span><b>type(string)</b>       － 类型（例：weixin）</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>user_id(int)</b>           － 用户ID</span></li>
                <li><span><b>user_name(string)</b>      － 用户昵称</span></li>
                <li><span><b>user_img(string)</b>       － 用户头像</span></li>
            <!-- ---------------------------------------------用户长草、用户收藏----------------------------------------------- -->
            <h3 id="1005">添加收藏</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值collect</span></li>
                <li><span><b>user_id(int)</b>       － 用户id</span></li>
                <li><span><b>relation_id(int)</b>   － 关联id</span></li>
                <li><span><b>type(int)</b>          － 类型(1产品 2文章)</span></li>
            </ul>
            <!-- ---------------------------------------------用户点评  ----------------------------------------------- -->
            <h3 id="1006">用户点评</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值userComment</span></li>
                <li><span><b>user_id(int)</b>       － 用户id</span></li>
                <li><span><b>type(int)</b>          － (选填)类型1产品2文章</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>comment_id(int)</b>      － 评论id</span></li>
                <li><span><b>type(int)</b>            － 类型ID</span></li>
                <li><span><b>post_id(string)</b>      － 产品id</span></li>
                <li><span><b>comment(string)</b>      － 内容</span></li>
                <li><span><b>created_at(int)</b>      － 时间</span></li>
                <li><span>&nbsp;&nbsp;&nbsp;&nbsp;以下为产品详情</span></li>
                <li><span><b>id(int)</b>            － 对应ID</span></li>
                <li><span><b>name(string)</b>       － 名称</span></li>
                <li><span><b>img(string)</b>        － 图片</span></li>
                <li><span><b>price(int)</b>         － 价格</span></li>
                <li><span><b>form(string)</b>       － 规格</span></li>
                <li><span><b>star(int)</b>          － 星级</span></li>
                <li><span>&nbsp;&nbsp;&nbsp;&nbsp;以下为文章详情</span></li>
                <li><span><b>name(string)</b>       － 名称</span></li>
                <li><span><b>img(string)</b>        － 图片</span></li>
                <li><span><b>id(int)</b>            － 对应ID</span></li>
                <li><span><b>linkUrl(string)</b>    － 链接地址</span></li>
                <li><span><b>comment_num(int)</b>   － 评论数</span></li>
                <li><span><b>isGras(int)</b>        － 0未收藏，1已收藏</span></li>
            <!-- ---------------------------------------------用户消息  ----------------------------------------------- -->
            <h3 id="1007">用户消息</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>     － 固定值userPms</span></li>
                <li><span><b>user_id(int)</b>        － 用户id</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>id(int)</b>                － 消息ID</span></li>
                <li><span><b>relation_id(int)</b>       － 关联ID</span></li>
                <li><span><b>content(string)</b>        － 内容</span></li>
                <li><span><b>created_at (string)</b>    － 时间</span></li>
                <li><span><b>img(string)</b>            － 用户头像</span></li>
                <li><span><b>user_name(string)</b>      － 用户名</span></li>
                <li><span><b>type(string)</b>           － 消息类型 （1 用户消息 2 系统消息）</span></li>
                <li><span><b>otype(string)</b>          － 类型 （0系统消息  1 产品 2 文章)</span></li>
            <!-- ---------------------------------------------用户重置密码  ----------------------------------------------- -->
            <h3 id="1008">用户重置密码</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值resetPassword</span></li>
                <li><span><b>mobile(string)</b>         － 手机号码</span></li>
                <li><span><b>captcha(int)</b>           － 验证码</span></li>
                <li><span><b>newPassword(string)</b>    － 新密码</span></li>
            </ul>
            <!-- ---------------------------------------------用户资料修改  ----------------------------------------------- -->
            <h3 id="1009">用户资料修改</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值userUpdate</span></li>
                <li><span><b>user_id(int)</b>            － 用户ID</span></li>
                <li><span><b>attribute(int)</b>         － 需要修改的字段(username,mobile,birthday,img)</span></li>
                <li><span><b>content(string)</b>        － 修改的值</span></li>
            </ul>
            <!-- ---------------------------------------------用户在用列表  ----------------------------------------------- -->
            <h3 id="1010">用户在用列表</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值userProduct</span></li>
                <li><span><b>user_id(int)</b>            － 用户ID</span></li>
                <li><span><b>page(int)</b>              － 当前页数</span></li>
                <li><span><b>pageSize(string)</b>       － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>user_id(int)</b>       － 用户ID</span></li>
                <li><span><b>brand_id(int)</b>      － 品牌ID</span></li>
                <li><span><b>brand_name(string)</b> － 品牌名</span></li>
                <li><span><b>product(string)</b>    － 产品名</span></li>
                <li><span><b>is_seal(int)</b>       － 是否开封,0未开封，1已开封</span></li>
                <li><span><b>seal_time(int)</b>     － 开封时间</span></li>
                <li><span><b>quality_time(int)</b>  － 开封后保质期  -- 保质期，单位为月</span></li>
                <li><span><b>overdue_time(int)</b>  － 过期时间(未开封为生产日期+3年，已开封为开封时间加保质期时间。)</span></li>
            <!-- ---------------------------------------------操作用户在用信息  ----------------------------------------------- -->
            <h3 id="1011">操作用户在用信息</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值operateUserproduct</span></li>
                <li><span><b>id(int)</b>                － 用户在用的ID</span></li>
                <li><span><b>type(int)</b>              － 操作类型(1开封 2删除)</span></li>
            </ul>
            <!-- ---------------------------------------------用户收藏--------------------------------------------------- -->
            <h3 id="1012">用户收藏接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值userCollect</span></li>
                <li><span><b>user_id(int)</b>           － 用户ID</span></li>
                <li><span><b>page(int)</b>              － 当前页数</span></li>
                <li><span><b>pageSize(string)</b>       － 每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>article_img(string)</b>    － 配图</span></li>
                <li><span><b>created_at(string)</b>     － 创建时间</span></li>
                <li><span><b>title(string)</b>          － 文章标题</span></li>
                <li><span><b>like_num(int)</b>          － 点赞数</span></li>
                <li><span><b>likeUrl(string)</b>        － 文章链接</span></li>
                <li><span><b>comment_num(int)</b>       － 评论数</span></li>
                <li><span><b>isGras(int)</b>            － 是否收藏或长草，1为是，0为否</span></li>
                <li><span><b>add_time(array)</b>        － 收藏时间</span></li>
            <!-- ---------------------------------------------大家都在搜--------------------------------------------------- -->
            <h3 id="1013">大家都在搜</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值searchHot</span></li>
                <li><span><b>page(int)</b>              － （非必填）当前页数</span></li>
                <li><span><b>pageSize(string)</b>       － （非必填）每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>name(string)</b>           － 关键字名称</span></li>
                <li><span><b>num(int)</b>               － 搜索次数</span></li>
            <!-- ---------------------------------------------搜索联想--------------------------------------------------- -->
            <h3 id="1014">搜索联想接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值searchAssociate</span></li>
                <li><span><b>keywords(string)</b>       － 关键词</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>type(int)</b>              － 类型 1 产品 2 分类</span></li>
                <li><span><b>name(string)</b>           － 名称</span></li>
            <!-- ---------------------------------------------搜索结果--------------------------------------------------- -->
            <h3 id="1015">搜索结果接口</h3>
            <p>调用参数：</p>
            <ul>
                <li><span><b>action(string)</b>         － 固定值searchQuery</span></li>
                <li><span><b>keywords(string)</b>       － （非必填）关键词</span></li>
                <li><span><b>type(int)</b>              － （非必填）类型 ：不传或者传1为默认搜索产品</span></li>
                <li><span><b>cate_id(int)</b>           － （非必填）分类id</span></li>
                <li><span><b>effect(string)</b>         － （非必填）功效关键字</span></li>
                <li><span><b>page(int)</b>              － （非必填）当前页数</span></li>
                <li><span><b>pageSize(string)</b>       － （非必填）每页多少条</span></li>
            </ul>
            <p>附加说明：</p>
                <li><span><b>data(array)</b>            － 产品列表详情</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>                － 文章ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_name(string)</b>   － 标题</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>product_img(string)</b>    － 配图</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>price(int)</b>             － 价格</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>form(string)</b>           － 规格</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>star(int)</b>              － 星级</span></li>
                <li><span><b>categories(array)</b>      － 产品分类列表</span></li> 
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>id(int)</b>                － 分类ID</span></li>
                <li>&nbsp;&nbsp;&nbsp;&nbsp;<span><b>cate_name(string)</b>      － 分类名称</span></li>
                <li><span><b>effects(array)</b>         － 功效列表（一维数组）</span></li> 
                <li><span><b>pageTotal(int)</b>         － 总数</span></li>
                <li><span><b>pageSize(int)</b>          － 当前页数</span></li>
            <!-- ---------------------------------------------公共参数--------------------------------------------------- -->

            <h3 id="097">推送说明</h3>
            <p>参数列表：</p>
            <ul>
                <li><span><b>type(int)</b>                － 1为过期提醒，2为H5，3为文章,4为产品，0为常规</span></li>
                <li><span><b>relation(int或string)</b>    － 对应的ID或URL ，1无，2为URL，3为文章ID，4为产品ID，0为无</span></li>
            </ul>
            <!-- ---------------------------------------------公共参数--------------------------------------------------- -->

            <h3 id="098">公共参数</h3>
            <p>参数列表：</p>
            <ul>
                <li><span><b>from(string)</b>   － 类型，android或ios</span></li>
                <li><span><b>time(int)</b>      － 当前时间截</span></li>
            </ul>
            <!-- ---------------------------------------------公共返回值--------------------------------------------------- -->
            <h3 id="z">公共返回值</h3>
            <ul>
                <li><span><b>&nbsp;0</b>－  (全局)失败</span></li>
                <li><span><b>&nbsp;1</b>－  (全局)成功</span></li>
                <li><span><b>-1</b>     －  (登录)帐号或密码错误</span></li>
                <li><span><b>-2</b>     －  (登录)账户不存在</span></li>
                <li><span><b>-3</b>     －  (全局)参数不完整</span></li>
                <li><span><b>-4</b>     －  (注册)注册失败</span></li>
                <li><span><b>-5</b>     －  (全局)方法为空</span></li>
                <li><span><b>-6</b>     －  (全局)TOKEN错误</span></li>
                <li><span><b>-7</b>     －  (全局)ip受限</span></li>
                <li><span><b>-8</b>     －  (全局)产品不存在</span></li>
                <li><span><b>-9</b>     －  (全局)时间过期</span></li>
                <li><span><b>-10</b>    －  (全局)帐号已被封号</span></li>
                <li><span><b>-11</b>    －  (全局)帐号异常</span></li>
                <li><span><b>-12</b>    －  (全局)验证码无效或已过期</span></li>
                <li><span><b>-13</b>    －  (全局)短信发送失败</span></li>
                <li><span><b>-14</b>    －  (全局)手机格式有误</span></li>
                <li><span><b>-15</b>    －  (全局)评论不存在</span></li>
                <li><span><b>-16</b>    －  (全局)文章不存在</span></li>
                <li><span><b>-17</b>    －  (全局)用户已存在</span></li>
                <li><span><b>-18</b>    －  (全局)用户已禁言</span></li>
                <li><span><b>-200</b>   －  (全局)其他错误</span></li>
            </ul>
            
            

            <p>返回形式:</p>
            <p class="subp">json字符串，形式如下： </p>
            <p class="subp">   array(
                'status' => '1',          // 返回代码
                'msg'    => 'msg',        // 具体说明
            )</p>
        </div>
    </body>
</html>