/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function(){
    // 绑定表情
    $('.face-icon').SinaEmotion($('.word_content'));

    var sinaEmotions;
    
//    $(".word_content").on('input',words_deal);
    
    
    $('.field-post-options #add_options').click(function(){
        console.log(this)
        //$(this).before('<input type="text" id="post-views_num" class="form-control" name="VoteCreate[options][]">');
        var data = '<input type="text" id="post-views_num" class="form-control option-list" name="VoteCreate[options][]"  style="width:500px;"><br/>';
        $(this).before(data);
        //$('.field-post-options').append(data);
        return false;
    });
})
function words_deal(){
//      var word_content = $('.word_content');
//      var max_count = word_content.attr('data-count');
//      var curLength=word_content.val().length;
//      if(curLength>max_count){
//        var num=word_content.val().substr(0,max_count);
//        word_content.val(num);
//        $(".err .jszs").hide();
//        $(".js-err").show();
//      }else{
//          $(".err .jszs").show();
//        $(".err .jszs").text('您还能输入' + (max_count-word_content.val().length) + '个字');
//        $(".js-err").hide();
//      }
    };