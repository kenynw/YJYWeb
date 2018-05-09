<?php 
use yii\helpers\Url;
?>
<iframe src="<?=Url::to(['swoole', 'user_id' => $user_id,'user_img' => $user_img]);?>" width="580" height="500" style="border:0;"></iframe>
