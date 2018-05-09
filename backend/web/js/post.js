/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function(){
    // 绑定表情
    $('.post-face-icon').SinaEmotion($('#post-content'));

    var sinaEmotions;

    $('#comment-face-icon').SinaEmotion($('#comment-comment'));

//    $("#post-content").on('input',words_deal);
})
//取消字数限制
function words_deal(){
//  var curLength=$("#post-content").val().length;
//  if(curLength>1000){
//    var num=$("#post-content").val().substr(0,1000);
//    $("#post-content").val(num);
//    $(".err .jszs").hide();
//    $(".js-err").show();
//  }else{
//      $(".err .jszs").show();
//    $(".err .jszs").text('您还能输入' + (1000-$("#post-content").val().length) + '个字');
//    $(".js-err").hide();
//  }
};