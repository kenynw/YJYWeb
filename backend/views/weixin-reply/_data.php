<?php 
use yii\helpers\Html;
use common\functions\Tools;
?>

<style>
.wx_pagination li {
	float: right;
	list-style: none;
}
.wx_pagination button {
	z-index: 3;
	border: 1px solid #ddd;
	position: relative;
	float: left;
	padding: 6px 12px;
	margin-left: 5px;
	line-height: 1.42857143;
	text-decoration: none;
	background-color: #fff;
	border-radius: 4px;
	color: #777;
}
.wx_pagination span {
	float: left;
	padding: 6px 12px;
}
.wx_pagination a {
	cursor: pointer;
}

.weui-desktop-appmsg {  position: relative;}.weui-desktop-appmsg .weui-desktop-mask {  display: none;  color: #FFFFFF;}.weui-desktop-appmsg .weui-desktop-card__inner {  padding: 0;}.weui-desktop-appmsg .weui-desktop-card__ft {  padding-left: 15px;  padding-right: 15px;}.weui-desktop-appmsg:hover .weui-desktop-appmsg__opr {  opacity: 1;  visibility: visible;}.weui-desktop-appmsg__cover-item {  word-wrap: break-word;  -webkit-hyphens: auto;  -ms-hyphens: auto;  hyphens: auto;  position: relative;  padding: 20px 15px 15px;}.weui-desktop-appmsg__cover-item:not(:last-child) {  padding: 12px 15px;}.weui-desktop-appmsg__cover-item:not(:last-child).weui-desktop-appmsg__cover_thumb {  position: relative;}.weui-desktop-appmsg__cover-item:not(:last-child).weui-desktop-appmsg__cover_thumb .weui-desktop-appmsg__cover__title {  color: #FFFFFF;  background-color: rgba(0, 0, 0, 0.55);  position: absolute;  left: 15px;  right: 15px;  bottom: 12px;  padding: 8px 12px;}.weui-desktop-appmsg__cover-item:not(:last-child).weui-desktop-appmsg__cover_thumb .weui-desktop-appmsg__cover__thumb {  margin-top: 0;}.weui-desktop-appmsg__cover-item:hover .weui-desktop-mask_preview {  display: block;}.weui-desktop-appmsg__cover__title {  font-size: 16px;  font-weight: 400;  display: block;  line-height: 1.2;  color: #353535;}.weui-desktop-appmsg__cover__thumb {  width: 100%;  height: auto;  background-size: cover;  background-position: 50% 50%;  background-repeat: no-repeat;  background-color: #F6F8F9;  padding-bottom: 55.55555556%;  display: block;  margin-top: 20px;}.weui-desktop-appmsg__cover__desc {  padding-top: 12px;  padding-bottom: 12px;  color: #9A9A9A;}.weui-desktop-appmsg__item:after {  content: "\200B";  display: block;  height: 0;  clear: both;}.weui-desktop-appmsg__item:hover .weui-desktop-mask_preview {  display: block;}.weui-desktop-appmsg__item {  padding: 12px 15px;  position: relative;}.weui-desktop-appmsg__item:before {  content: " ";  position: absolute;  top: 0;  left: 15px;  right: 15px;  border-top: 1px solid #E4E8EB;}.weui-desktop-appmsg__thumb {  float: right;  margin-left: 12px;  width: 60px;  height: 60px;  background-size: cover;  background-position: 50% 50%;  background-repeat: no-repeat;  background-color: #F6F8F9;}.weui-desktop-appmsg__title {  overflow: hidden;  font-weight: 400;  word-wrap: break-word;  -webkit-hyphens: auto;  -ms-hyphens: auto;  hyphens: auto;  color: #353535;}.weui-desktop-appmsg__opr {  float: right;  opacity: 0;  visibility: hidden;  transition: opacity .3s;}.weui-desktop-appmsg__tips {  color: #9A9A9A;}.weui-desktop-dialog .weui-desktop-appmsg .weui-desktop-mask_preview {  display: none;}.weui-desktop-dialog .weui-desktop-appmsg.selected .weui-desktop-mask_msg,.weui-desktop-dialog .weui-desktop-appmsg:hover .weui-desktop-mask_msg {  display: block;}.weui-desktop-appmsg__cover__title em,.weui-desktop-appmsg__title em {  color: #1AAD19;  font-style: normal;}
</style>

<?=Html::cssFile('@web/css/wx-material.css')?>
<?php 
    if ($materialList['list']) { 
        if ($materialList['type'] == 'image') { ?>
<div style="width: 100%; overflow: hidden; padding-top: .5rem;">
	<ul class="wx_list" style="width: 100%;">
		<li
			style="float: left; margin: 0 0 .3rem .5rem; position: relative; border-radius: .2rem; list-style: none;">
                        <?php foreach ($materialList['list'] as $key=>$val) { ?>
                                <img alt="" src="<?=$val['url'] ?>"
			style="width: 200px; height: 200px" data-id=<?=$val['media_id'] ?>>
                        <?php } ?>
                    </li>
	</ul>
</div>
<?php } elseif ($materialList['type'] == 'news') { ?>
<div class="weui-desktop-panel__bd">
	<div class="weui-desktop-panel weui-desktop-panel_transparent">
		<div class="weui-desktop-panel__bd">
			<div class="weui-desktop-media__list-wrp">
				<div id="js_card" class="weui-desktop-media__list weui-desktop-tj">
					<div class="weui-desktop-media__list-col weui-desktop-tj__item">
						<div id="js_col1" class="inner">
							<?php foreach ($materialList['list'] as $key=>$val) { ?>
							<div style="display: inline-block;padding:6px">
								<div data-appid="<?=$val['media_id'] ?>" class="weui-desktop-card weui-desktop-appmsg click-box">
									<div class="weui-desktop-card__inner">
										<div class="weui-desktop-card__bd">
											<!---->
											<?php foreach ($val['content']['news_item'] as $key2=>$val2) { ?>
											<?php if ($key2 == 0) { ?>
											<div class="weui-desktop-appmsg__cover-item weui-desktop-appmsg__cover_thumb">
												<span href="" target="_blank" class="weui-desktop-appmsg__cover__title"><?=$val2['title'] ?></span>
												<i class="weui-desktop-appmsg__cover__thumb"
													style="background-image: url(&quot;<?=$val2['thumb_url'] ?>&quot;);"></i>
												<div
													class="weui-desktop-mask weui-desktop-mask_status weui-desktop-mask_preview">
													<a href="<?=$val2['url'] ?>" target="_blank"> 预览文章 </a>
												</div>
											</div>
											<?php } else { ?>
											<div class="weui-desktop-appmsg__item">
												<i class="weui-desktop-appmsg__thumb"
													style="background-image: url(&quot;<?=$val2['thumb_url'] ?>&quot;);"></i>
												<span href="" target="_blank" class="weui-desktop-appmsg__title"><?=$val2['title']?></span>
												<div class="weui-desktop-mask weui-desktop-mask_status weui-desktop-mask_preview">
													<a href="<?=$val2['url'] ?>" target="_blank"> 预览文章 </a>
												</div>
											</div>
                                            <?php } ?>
                                            <?php } ?>
										</div>
										<div class="weui-desktop-card__ft">
											<div class="weui-desktop-appmsg__tips mini_tips icon_after">
												更新于 <?=date('Y-m-d H:i:s',$val['update_time']) ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					&nbsp;
				</div>
				<!---->
			</div>
		</div>
	</div>
</div>
<?php } ?>
<div>
	<ul class="wx_pagination" data-type="<?=$materialList['type'] ?>" style="margin-right: 45%;">
		<li class="go"><span style="padding: 3px 12px;"><input size="3" style="height: 25px; margin-right: 5px"><a>Go</a></span></li>
		<li class="next click" style="display: <?=$materialList['pageMsg']['page'] == '1' ||  $materialList['pageMsg']['page'] != $materialList['pageMsg']['pageCount'] ? 'block' : 'none' ?>"><button type="button">»</button></li>
		<li class="page"><span data-page=<?=$materialList['pageMsg']['page'] ?>><?=$materialList['pageMsg']['page'] ?>/<?=$materialList['pageMsg']['pageCount'] ?></span></li>
		<li class="prev click" style="display: <?=$materialList['pageMsg']['page'] != '1' ||  $materialList['pageMsg']['page'] == $materialList['pageMsg']['pageCount'] ? 'block' : 'none' ?>"><button type="button">«</button></li>
	</ul>
</div>
<?php

}
?>

<?php
$pageCount = $materialList['pageMsg']['pageCount'];
$script = <<<JS

JS;
$this->registerJs($script);
?>
