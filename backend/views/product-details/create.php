<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use backend\models\CommonFun;
use yii\base\Object;
use common\models\ProductCategory;
use common\models\ProductComponent;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\ProductDetails */

$this->title = '添加产品页';
$this->params['breadcrumbs'][] = ['label' => '产品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-details-create">
    <?= $this->render('_form', [
            'model' => $model,
            'cateList' => $cateList,
            'searchModel2' => $searchModel2,
            'dataProvider2' => $dataProvider2,
    ]) ?>
</div>
