<?php

use yii\helpers\Html;
use common\models\ProductDetails;

$this->title = '批量添加';
$this->params['breadcrumbs'][] = ['label' => '产品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?=Html::cssFile('@web/css/loading/ladda-themeless.min.css')?>

<h2>推广链接添加</h2><br/>

<div style="margin:20px;">
	<form action="insert-excel" method="post" enctype="multipart/form-data">
		<input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">

			<span style="margin-top:4px;float:left">推广链接文件：</span><input type="file" name="file"  size="50" />

		<br/><br/>
		<button type="submit" class="btn btn-success ladda-button"data-style="expand-left">提交</button>
	</form>
</div>
<?php if(isset($result) && $result){?>
    <div style="margin:20px;">
        <strong>已导入<?=$result['total']?>个返利链接，其中成功<?=$result['success'] ?>条，失败<?=$result['total']-$result['success'] ?>条。</strong>
        <br><br>
        <table class="table table-striped table-bordered">
            <tr><th>颜究院ID</th><th>淘宝ID</th><th>淘宝状态</th><th>京东ID</th><th>京东状态</th></tr>
            <?php foreach ($result as $key=>$val) { ?>
                <tr><td><?=$val['product_id'] ?></td><td><?=$val['tb_id'] ?></td><td><?=$val['tbStatus']?></td><td><?=$val['jd_id']?></td><td><?=$val['jdStatus']?></td></tr>
            <?php } ?>
        </table>
    </div>
<?php } ?>







