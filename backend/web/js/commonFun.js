//ajax change status
function status_ajax(statusurl,refresh=0){
	$(document).on("click",'.btnstatus',function(){
		var id = $(this).attr("data-id");
		var status = $(this).attr("data-status");
	    var type = $(this).attr("data-type");
	    var url = statusurl;
	    var box = $(this);
	    
	    switch(type)
	    {
		    case "is_recommend":
		    	if(status == '0') {
			        var btnval = '已推荐';
		    	} else {
			        var btnval = '默认';
		    	}
		        break;
		    case "status":
		    	if(status == '0') {
			        var btnval = '已上架';
		    	} else {
			        var btnval = '已下架';
		    	}
		        break;
		    case "is_digest":
		    	if(status == '0') {
			        var btnval = '精华';
		    	} else {
			        var btnval = '默认';
		    	}
		        break;
		    case "is_top":
		    	if(status == '0') {
			        var btnval = '已上榜';
		    	} else {
			        var btnval = '默认';
		    	}
		        break;
	    }
	    
	    $.ajax({
	        url: url,
	        type: 'post',
	        dataType: 'json',
	        data:{id:id,status:status,type:type},
	        success : function(data) {
	            if (data.status == "1") {
	                if(status == 1){
	                    var d = "<button class='btn btn-xs btnstatus' data-status='0' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
	                }else{
	                    var d = "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
	                }
	                box.parent("td").html(d);

					if(refresh){
						window.location.reload();
					}
	                //alert('操作成功！');
	            }
	        },
	        beforeSend : function(data) {
	            box.text("loading...");
	        },
	        error : function(data) {
	            alert('操作失败！');
	        }
	    });
	});	
}
//记录多选数据
function check_list(id,store){
    
    var news = [];
    var dif =[];    
    $(id+" input[name='id[]']").each(function(){
        if($(this).is(':checked')){
            news.push($(this).val());   
        }else{
            dif.push($(this).val());
        }
    });
    
    var old =  $(store).html();
    
    if(old){
        old = old.split("-");
        //添加新数据
        for( i= 0; i< news.length; i++){ 
            if(!inArray(old, parseInt(news[i]) )){
                old.push(parseInt(news[i]));
            }
        }
        
        //删除旧数据
        for( i= 0; i< dif.length; i++){ 
            if(j = inArray(old, parseInt(dif[i]) )){
                old.splice(j-1,1);
            }
        }
    }else{
        old = news;
    }
    
    console.log(old);
    if(old){
        var num = old.length;
        if(num){
            $("#product_num").html("已选择<span style='color:red;font-weight:bold'>" + num + "</span>个产品");
        }else{
            $("#product_num").html("");
        }

        old = old.join("-");
        $(store).html(old);
    }
}






//js prevent submit repeated
$('form').on('beforeValidate', function (e) {
	id = $(this).attr('id');
	if (id != 'prevent-disabled') {
	    $(':submit').attr('disabled', true).addClass('disabled');
	}
});
$('form').on('afterValidate', function (e) {
    if (cheched = $(this).data('yiiActiveForm').validated == false) {
        $(':submit').removeAttr('disabled').removeClass('disabled');
    }
});
$('form').on('beforeSubmit', function (e) {
	id = $(this).attr('id');
	if (id != 'prevent-disabled') {
	    $(':submit').attr('disabled', true).addClass('disabled');
	}
});
//捕获url参数
function get_UrlParams(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}
//验证url格式
function checkUrl(url){
	var Expression=/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
    var objExp=new RegExp(Expression);

    if(objExp.test(url) != true){
        return false;
    } else {
    	return true;
    }

}
//保留两位小数百分数
function toPercent(point){
    var str=Number(point*100).toFixed(2);
    str+="%";
    return str;
}
//反php序列化
function unserialize(ss) {
    var p = 0, ht = [], hv = 1;
    r = null;
    function unser_null() {
        p++;
        return null;
    }
    function unser_boolean() {
        p++;
        var b = ss.charAt(p++) == "1";
        p++;
        return b;
    }
    function unser_integer() {
        p++;
        var i = parseInt(ss.substring(p, p = ss.indexOf(";", p)));
        p++;
        return i;
    }
    function unser_double() {
        p++;
        var d = ss.substring(p, p = ss.indexOf(";", p));
        switch (d) {
          case "INF":
            d = Number.POSITIVE_INFINITY;
            break;

          case "-INF":
            d = Number.NEGATIVE_INFINITY;
            break;

          default:
            d = parseFloat(d);
        }
        p++;
        return d;
    }
    function unser_string() {
        p++;  
        var l = parseInt(ss.substring(p, p = ss.indexOf(':', p)));   
        p += 2;   
        var s = subChnStr(ss,l,p);  
        p += s.length;  
        p += 2;   
        return s;   
    }
    function unser_array() {
        p++;
        var n = parseInt(ss.substring(p, p = ss.indexOf(":", p)));
        p += 2;
        var a = [];
        ht[hv++] = a;
        for (var i = 0; i < n; i++) {
            var k;
            switch (ss.charAt(p++)) {
              case "i":
                k = unser_integer();
                break;

              case "s":
                k = unser_string();
                break;

              case "U":
                k = unser_unicode_string();
                break;

              default:
                return false;
            }
            a[k] = __unserialize();
        }
        p++;
        return a;
    }
    function unser_object() {
        p++;
        var l = parseInt(ss.substring(p, p = ss.indexOf(":", p)));
        p += 2;
        var cn = subChnStr(ss,l,p);  
        p += cn.length;  
        p += 2;
        var n = parseInt(ss.substring(p, p = ss.indexOf(":", p)));
        p += 2;
        if (eval([ "typeof(", cn, ') == "undefined"' ].join(""))) {
            eval([ "function ", cn, "(){}" ].join(""));
        }
        var o = eval([ "new ", cn, "()" ].join(""));
        ht[hv++] = o;
        for (var i = 0; i < n; i++) {
            var k;
            switch (ss.charAt(p++)) {
              case "s":
                k = unser_string();
                break;

              case "U":
                k = unser_unicode_string();
                break;

              default:
                return false;
            }
            if (k.charAt(0) == "\0") {
                k = k.substring(k.indexOf("\0", 1) + 1, k.length);
            }
            o[k] = __unserialize();
        }
        p++;
        if (typeof o.__wakeup == "function") o.__wakeup();
        return o;
    }
    function unser_custom_object() {
        p++;
        var l = parseInt(ss.substring(p, p = ss.indexOf(":", p)));
        p += 2;
        var cn = subChnStr(ss,l,p);  
        p += cn.length;  
        p += 2;
        var n = parseInt(ss.substring(p, p = ss.indexOf(":", p)));
        p += 2;
        if (eval([ "typeof(", cn, ') == "undefined"' ].join(""))) {
            eval([ "function ", cn, "(){}" ].join(""));
        }
        var o = eval([ "new ", cn, "()" ].join(""));
        ht[hv++] = o;
        if (typeof o.unserialize != "function") p += n; else o.unserialize(ss.substring(p, p += n));
        p++;
        return o;
    }
    function unser_unicode_string() {
        p++;
        var l = parseInt(ss.substring(p, p = ss.indexOf(":", p)));
        p += 2;
        var sb = [];
        for (i = 0; i < l; i++) {
            if ((sb[i] = ss.charAt(p++)) == "\\") {
                sb[i] = String.fromCharCode(parseInt(ss.substring(p, p += 4), 16));
            }
        }
        p += 2;
        return sb.join("");
    }
    function unser_ref() {
        p++;
        var r = parseInt(ss.substring(p, p = ss.indexOf(";", p)));
        p++;
        return ht[r];
    }
    function __unserialize() {
        switch (ss.charAt(p++)) {
          case "N":
            return ht[hv++] = unser_null();

          case "b":
            return ht[hv++] = unser_boolean();

          case "i":
            return ht[hv++] = unser_integer();

          case "d":
            return ht[hv++] = unser_double();

          case "s":
            return ht[hv++] = unser_string();

          case "U":
            return ht[hv++] = unser_unicode_string();

          case "r":
            return ht[hv++] = unser_ref();

          case "a":
            return unser_array();

          case "O":
            return unser_object();

          case "C":
            return unser_custom_object();

          case "R":
            return unser_ref();

          default:
            return false;
        }
    }
    return __unserialize();
}
//gbk 与utf8中文占的字符个数不一样，需要处理
function subChnStr(str, len, start, hasDot)  
{  
    var newLength = 0;  
    var newStr = "";  
    var chineseRegex = /[^\x00-\xff]/g;  
    var singleChar = "";  
    var strLength = str.replace(chineseRegex,"**").length;  
      
    for(var i = start;i < strLength;i++)  
    {  
        singleChar = str.charAt(i).toString();  
        if(singleChar.match(chineseRegex) != null)  
        {  
            newLength += 2;  
        }      
        else  
        {  
            newLength++;  
        }  
        if(newLength > len)  
        {  
            break;  
        }  
        newStr += singleChar;  
    }  
      
    if(hasDot && strLength > len)  
    {  
        newStr += "...";  
    }  
    return newStr;  
}  
