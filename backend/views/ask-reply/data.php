<?php 
use yii\helpers\Html;
use common\functions\Tools;
?>
<div style="background-color: #F2F2F2;width:100%;margin-top:10px;padding:0px 15px 15px 15px;" id="reply-box"><?php foreach($reply_list as $val){ ?><div style="width:100%;">
<a href="/user/view?id=<?=$val['user_id']?>" target="_blank"><span style="color:<?= $val['admin_id'] == 0 ? 'green' : 'blue'?>"><?= Tools::userTextDecode($val['username']) ?></span></a> 回复：<?= Tools::userTextDecode($val['reply']) ?><?=empty($val['picture']) ? false : '&nbsp;'.Html::a('查看图片','javascript:void(0)',['data-url' => Yii::$app->params['uploadsUrl'].$val['picture'],'class' => 'ask-img','data-toggle' => 'modal','data-target' => "#ask-img"]);?>&emsp;<span style="color:#AEAEAE;font-size:12px;"><?= date('Y-m-d H:i:s', $val['add_time']) ?></span><button id="del-reply" data-replyid="<?= $val['replyid'] ?>" style="cursor:pointer;color:#AEAEAE;font-size:12px;border:0px">删除</button>
</div><?php }?>
</div>