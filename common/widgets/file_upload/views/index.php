<?php
/**
 * @see Yii中文网  http://www.yii-china.com
 * @author Xianan Huang <Xianan_huang@163.com>
 * 图片上传组件
 * 如何配置请到官网（Yii中文网）查看相关文章
 */
 
use yii\helpers\Html;
?>
<div class="per_upload_con" data-url="<?=$config['serverUrl']?>">
    <div class="per_real_img <?=$attribute?>" domain-url = "<?=$config['domain_url']?>"><?=isset($inputValue)?'<img src="'.$config['domain_url'].$inputValue.'" onerror="this.style.display=\'none\'">':''?></div>
    <div class="per_upload_img">
        <b>推荐大小：</b>小于1M<br/>
        <?= !empty($config['explain']) ? $config['explain'] : '123x123'?>
    </div>

    <div class="per_upload_text">
        <p class="upbtn" style="margin-left: 35%" ><a id="<?=$attribute?>" href="javascript:;" class="btn btn-success green choose_btn btn-sm">上传</a></p>
    </div>
    <input id="<?=$inputId?>" up-id="<?=$attribute?>" type="hidden" name="<?=$inputName?>" upname='<?=$config['fileName']?>' value="<?=isset($inputValue)?$inputValue:''?>" filetype="img" class="form-control"/>
</div>
