<?php

use yii\helpers\Html;

$this->title = '批量添加';
$this->params['breadcrumbs'][] = ['label' => '产品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?=Html::cssFile('@web/css/loading/ladda-themeless.min.css')?>

<h2>产品批量添加</h2><br/>

<div style="color:red">
	<?= $msg ?>
</div>

<div style="margin:20px;">
	<form method="post">
		<input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
<!--		开始页：<input type="text" name="page" value="1" /><br/><br/>-->
<!--		结束页：<input type="text" name="pageSize" value="255" /><br/><br/>-->
<!--		分类id：<input type="text" name="cateId" /><br/><br/>-->
		产品名：<input type="text" name="keyword" size="50" /><br/><br/>
        开始页：<input type="text" name="min" size="50" /><br/><br/>
        结束页：<input type="text" name="max" size="50" /><br/><br/>
<!--		模糊匹配：-->
<!--		<input type="radio" name="is_dim" value="1"/>是-->
<!--		<input type="radio" name="is_dim" value="0" checked />否-->
<!--		<br/><br/>-->
		<button type="submit" class="btn btn-success ladda-button"data-style="expand-left">提交</button>
	</form>
</div>    

<?=Html::jsFile('@web/js/loading/spin.min.js')?>
<?=Html::jsFile('@web/js/loading/ladda.min.js')?>
<?php 
$script = <<<JS
Ladda.bind('.ladda-button');
JS;
$this->registerJs($script);
?>






