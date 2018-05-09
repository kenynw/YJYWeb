<div style="background-color: #F2F2F2;width:100%;" id="reply-box"><?php foreach($reply_list as $val){ ?><div style="width:100%;">
<span style="color:#339900"><?= $val['username'] ?></span> 回复：<?= $val['reply'] ?><span style="color:#AEAEAE;font-size:12px;"><?= date('Y-m-d H:i:s', $val['add_time']) ?></span><a id="del-reply" style="color:#AEAEAE;font-size:12px;">删除</a>
</div><?php }?>
</div>