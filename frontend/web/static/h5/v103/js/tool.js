/**
*  @Author: chendianhuai (773189176@qq.com)
*  @Date:   2017-01-09 11:59:08
*  @Last Modified by:   wuyazhen
*  @Last Modified time: 2017-09-06 09:42:13
*/

/* 初始化单位  rem */
(function (doc, win) {
  var docEl = doc.documentElement,
    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
    recalc = function () {
      var clientWidth = docEl.clientWidth;
      if (!clientWidth) return;
      docEl.style.fontSize = 50 * (clientWidth / 320) + 'px';
    };

  if (!doc.addEventListener) return;
  win.addEventListener(resizeEvt, recalc, false);
  doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);
$(function(){
	$('body').height($(window).height());
	// 关闭弹窗
	$('.modal-bg,.js-colse').on('click',function(){
		$('.modal-bg,.modal').fadeOut(100);
	});

	$('.js-search').on('click',function(){
		$(this).blur();
		$('.search-input').blur();
		$('.search-modal').animate({left: 0}, 100);
	});
	$('.close-mod').on('click',function(){
		$('.search-modal').animate({left: '100%'}, 100);
		$('.s-mod-box').scrollTop(0);
	});
	$('.jy-search ul').on('click','li',function(){
		$('.search-modal').animate({left: '100%'}, 100);
		$('.inp-name').text($(this).text()).css('color','#333');
		$('.inp-ph').val('');
		$('#cosid').val($(this).attr('cid'))
	});
	$('.close-sear').on('click',function(){
		$('.search-input').val('');
		$(".jy-search ul li,.sea-tit,.jy-search ul:first").show();
		$(".jy-search .sea-result-tit,.sea-result").hide();
	});
	$('.inp-ph').on('focus',function(){
		var str= navigator.userAgent.toLowerCase();
	    var ver=str.match(/cpu iphone os (.*?) like mac os/);
	    if(parseInt(ver[1].replace(/_/g,"."))<10){
	        setTimeout(function(){
	            $(window).scrollTop($('.search-box').offset().top-10 );
	        },100)
	    }
	});

	$('.search-input').bind('input propertychange', function() {
	    var searchName = $(".search-input").val().toLowerCase();
	    var partit ='';
	    if (searchName == "" || searchName == null) {
			$(".search-ul li,.sea-tit,.jy-search ul:first").show();
	    	$('.jy-search .sea-result-tit,.sea-result').hide();
	    } else {
    		var search = 0;
    		var liLength = $(".search-ul li").length;
		    $(".search-ul li").each(function() {
		        var pinyin = $(this).attr("pinyin").toLowerCase();
		        var name = $(this).attr("name");
		        if (pinyin.indexOf(searchName) != -1
		            || name.indexOf(searchName) != -1) {
		        	partit = $(this).attr('data-class');
		        	$(this).show();
		        	search++;
		        } else {
		        	$(this).hide();
				}
				if(partit != null){
					$('#pdclass_'+partit).show();
				}
			});
			if (search>0 && search<=liLength) {
				$('.sea-result,.sea-tit,.jy-search ul:first').hide();
				$('.jy-search .sea-result-tit').show();
			}else if (search==0){
				$('.jy-search .sea-result-tit,.sea-tit,.jy-search ul:first').hide();
				$('.sea-result').show();
			}
	    }

	});

});















