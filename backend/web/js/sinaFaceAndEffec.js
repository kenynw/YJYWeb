/**
* @Author: chendianhuai ()
* @Date:   2016-07-15 15:16:29
* @Desc:   她们说 表情脚本
* @Last Modified by:   chendianhuai
* @Last Modified time: 2016-08-16 10:11:29
*/
// 初始化缓存，页面仅仅加载一次就可以了
var emotions 	= new Array();
var categorys 	= new Array();// 分组
var uSinaEmotionsHt = new Hashtable();
var data 	= [];

var cdnUrl 	= 'http://oss.yjyapp.com/static/pc';
data = sinaEmotions;
for ( var i in data) {
	if (data[i].category == '') {
		data[i].category = '默认';
	}
	if (emotions[data[i].category] == undefined) {
		emotions[data[i].category] = new Array();
		categorys.push(data[i].category);
	}
	emotions[data[i].category].push( {
		name : data[i].phrase,
		icon : data[i].icon
	});
	uSinaEmotionsHt.put(data[i].phrase, data[i].icon);
}
//自定义hashtable
function Hashtable() {
    this._hash = new Object();
    this.put = function(key, value) {
        if (typeof (key) != "undefined") {
            if (this.containsKey(key) == false) {
                this._hash[key] = typeof (value) == "undefined" ? null : value;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    };
    this.remove = function(key) { delete this._hash[key]; }
    this.size = function() { var i = 0; for (var k in this._hash) { i++; } return i; }
    this.get = function(key) { return this._hash[key]; }
    this.containsKey = function(key) { return typeof (this._hash[key]) != "undefined"; }
    this.clear = function() { for (var k in this._hash) { delete this._hash[k]; } }
}
//替换
function AnalyticEmotion(s) {
	if(typeof (s) != "undefined") {
		var sArr = s.match(/\[.*?\]/g);
		if(null!=sArr && '' != sArr){
			for(var i = 0; i < sArr.length; i++){
				if(uSinaEmotionsHt.containsKey(sArr[i])) {
					var reStr = "<img src=\"" + cdnUrl + uSinaEmotionsHt.get(sArr[i]) + "\" height=\"22\" width=\"22\" />";
					s = s.replace(sArr[i], reStr);
				}
			}
		}

	}
	return s;
}

(function($){
	$.fn.SinaEmotion = function(target){
		var cat_current;
		var cat_page;
		$(this).click(function(event){
			event.stopPropagation();

			var eTop = target.offset().top + target.height() + 15;
			var eLeft = target.offset().left - 1;

			if($('#emotions .categorys')[0]){

				$('#emotions').css({top: eTop, left: eLeft});
				$('#emotions').toggle();
				return;
			}
			$('body').append('<div id="emotions" style="width:586px;"></div>');
			$('#emotions').css({top: eTop, left: eLeft});
			$('#emotions').html('<div>正在加载，请稍候...</div>');
			$('#emotions').click(function(event){
				words_deal();
				event.stopPropagation();
			});
			$('#emotions').html('<div class="container" style="width:586px;"></div>');
			$('#emotions #prev').click(function(){
				showCategorys(cat_page - 1);
			});
			$('#emotions #next').click(function(){
				showCategorys(cat_page + 1);
			});
			showCategorys();
			showEmotions();

		});

		$('body').click(function(){
			$('#emotions').remove();
		});
	
	 
		$.fn.insertText = function(text){
			this.each(function() {
				if(this.tagName !== 'INPUT' && this.tagName !== 'TEXTAREA') {return;}
				if (document.selection) {
					this.focus();
					var cr = document.selection.createRange();
					cr.text = text;
					cr.collapse();
					cr.select();
				}else if (this.selectionStart || this.selectionStart == '0') {
					var
					start = this.selectionStart,
					end = this.selectionEnd;
					this.value = this.value.substring(0, start)+ text+ this.value.substring(end, this.value.length);
					this.selectionStart = this.selectionEnd = start+text.length;
				}else {
					this.value += text;
				}
			});
			return this;
		};
		function showCategorys(){
			var page = arguments[0]?arguments[0]:0;
			if(page < 0 || page >= categorys.length / 5){
				return;
			}
			cat_page = page;
		}
		function showEmotions(){
			var category = arguments[0]?arguments[0]:'默认';
			var page = arguments[1]?arguments[1] - 1:0;
			$('#emotions .container').html('');

			cat_current = category;
			for(var i = 0;  i < emotions[category].length; ++i){
				$('#emotions .container').append($('<a href="javascript:void(0);" title="' + emotions[category][i].name + '"><img src="' + cdnUrl+ emotions[category][i].icon + '" alt="' + emotions[category][i].name + '" width="22" height="22" /></a>'));
			}
			$('#emotions .container a').click(function(e){
				target.insertText($(this).attr('title'));
				$('.doc,.t-in').blur();
 
			});
		}
	};
})(jQuery);
