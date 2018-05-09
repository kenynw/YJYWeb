<?php

?>
<div><label>重置肤质测试结果</label></div>
<div style="float: left">
<input id="skin-test" class="form-control" placeholder="请输入用户id">
</div>
<a href="" class="btn btn-success skin-test-reset" style="margin-left: 5px" disabled="disabled" data-method="post" data-pjax="0" target="">重置</a>

<?php 
$script = <<<JS
//重置肤质测试
$("#skin-test").bind('input propertychange', function() {
    var skin_test_val = $('#skin-test').val().trim();
    if(skin_test_val == ''){
        $('.skin-test-reset').attr('disabled','disabled');
    }else{
        $('.skin-test-reset').attr('disabled',false);
        $('.skin-test-reset').attr('href','/beta/reset-skin-test?id='+skin_test_val);
    }
})

JS;
$this->registerJs($script);
?>