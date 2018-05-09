<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\ArticleCategory;

$cate_ids = isset($_GET['ArticleSearch']['cate_ids']) ? $_GET['ArticleSearch']['cate_ids'] : "";
$cate_id = isset($_GET['ArticleSearch']['cate_id']) ? $_GET['ArticleSearch']['cate_id'] : "";

?>

<div class="article-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div style="float:left;margin-right:80px;">
        <?php
        if(!$model->isNewRecord){
            $parent_id = ArticleCategory::find()->select("parent_id")->where(['id'=>$model->cate_id])->asArray()->column();
            $model->cate_ids = $parent_id;
            $list2 = yii\helpers\ArrayHelper::map(ArticleCategory::find()->where(['parent_id'=>$parent_id])->all(), 'id', 'cate_name');
        }
        ?>
        <?= $form->field($model, 'cate_ids')->dropDownList( yii\helpers\ArrayHelper::map(ArticleCategory::find()->all(), 'id', 'cate_name'),['style'=>'width:120px;','prompt'=>'--- 请选择 ---','value'=>$cate_ids])->label("文章一级分类") ?>
    </div>

    <div style="float:left;">
        <?= $form->field($model, 'cate_id')->dropDownList( isset($list2) ? $list2 : array('' => "--- 请选择 ---"),['style'=>'width:120px;','value'=>$cate_id])->label("文章二级分类") ?>
    </div>

    <div class="form-group" style="clear:both;">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
