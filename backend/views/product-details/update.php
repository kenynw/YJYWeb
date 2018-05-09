<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use common\models\ProductCategory;
use backend\models\CommonFun;
use common\models\ProductComponent;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;
use yii\base\Object;
use common\models\CommonTag;
use common\models\Brand;
/* @var $this yii\web\View */
/* @var $model common\models\ProductDetails */

$this->title = '编辑产品页';
$this->params['breadcrumbs'][] = ['label' => '产品详情页', 'url' => ['index']];
$this->params['breadcrumbs'][] = '产品编辑';
?>
<div class="product-details-update">
    <?= $this->render('_form', [
            'cateIdArr' => $cateIdArr,
            'tagIdArr' => $tagIdArr,
            'tagIdArr2' => $tagIdArr2,
            'cateList' => $cateList,
            'model' => $model,
    ]) ?>
</div>
