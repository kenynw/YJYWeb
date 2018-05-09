<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\ProductCategory;
use common\models\SkinRecommend;
use backend\models\CommonFun;
use common\models\ProductComponent;
use common\functions\Functions;

/* @var $this yii\web\View */
/* @var $model common\models\BrandCategory */

$this->title = $model->skin.'('.$model->explain.')';
$this->params['breadcrumbs'][] = ['label' => '肤质列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

//分类成分相关
//分类
$cateList = ProductCategory::find()->select('id,cate_name')->asArray()->all();
$componentStr = '';
$reskin = '';
$noreskin = '';
foreach ($cateList as $key=>$val) {
    //分类推荐成分
    $SkinRecommend = SkinRecommend::find()->select("reskin,noreskin,copy")->where("category_id = {$val['id']} AND skin_id = $model->id")->asArray()->all();  

    if ($key <= 7) {
        
        if (!empty($SkinRecommend)) {
            foreach ($SkinRecommend as $key2=>$val2) {
                if (!empty($val2['reskin'])) {
                    $reskin = join('，',CommonFun::getKeyValArr(new ProductComponent(), 'id', 'name',Functions::db_create_in(explode(',', $val2['reskin']),'id')));
                } else {
                    $reskin = '无';
                }
                if (!empty($val2['noreskin'])) {
                    $noreskin = join('，',CommonFun::getKeyValArr(new ProductComponent(), 'id', 'name',Functions::db_create_in(explode(',', $val2['noreskin']),'id')));
                } else {
                    $noreskin = '无';
                }
                if (!empty($val2['copy'])) {
                    $copy = $val2['copy'];
                } else {
                    $copy = '无';
                }
            }
        } else {
            $reskin = '无';
            $noreskin = '无';
            $copy = '无';
        }
        
        $componentStr .= "<div style = 'border:1px solid #d2d6de;width:100%;padding:10px;margin-top:20px'>
                          <label class='control-label' for=''>".$val['cate_name']."</label><br><br>
                          <label class='control-label' for=''>推荐成分</label><br>
                          <div style = 'border:1px solid #d2d6de;width:100%;padding:5px;margin-top:2px'>
                          ".$reskin."</div><br><label class='control-label' for=''>不推荐成分</label><div style = 'border:1px solid #d2d6de;width:100%;padding:5px;margin-top:2px'>".$noreskin."</div><br>
                          <label class='control-label' for=''>".$val['cate_name']."方案</label><div style = 'border:1px solid #d2d6de;width:100%;padding:5px;margin-top:2px'>".$copy."</div></div>";
    }
}
?>
<div class="brand-category-view">

    <p>
        <?= Html::a('编辑', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'skin',
                'value' => $model->skin.'('.$model->explain.')'
            ],
            'features',
            'elements',
            [
                'label' => '分类成分相关',
                'format' => 'raw',
                'value' => $componentStr
            ]

        ],
    ]) ?>

</div>
