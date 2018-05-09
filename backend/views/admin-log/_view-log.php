<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\AdminLogSearch;
use common\models\ProductLink;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AdminLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if ($type && $typeId) {
    $searchModel = new AdminLogSearch();
    $params = Yii::$app->request->queryParams;
    $params['type'] = $type;
    $params['id'] = $typeId;
    $dataProvider = $searchModel->viewSearch($params);
}
?>
<div class="admin-log-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '5%']
            ],

            [
                'attribute' => 'username',
                'options' => ['width' => '15%'],
                'value' => function ($model) {
                    return $model->username;
                },
                'filter' => false
            ],
            [
                'attribute' => 'description',
                'format' => 'html',
                'contentOptions' => ['style' => 'width:30%;word-break: break-all'],
                'value' => function ($model) use ($type,$typeId) {
                    $return = $model->description;
                    $route = $model->route;
                    
                    switch ($type) {
                        case 'brand':
                            if ($route == "/product-details/brand-add-product") {
                                if (preg_match ('/是否上榜/',$model->description)) {
                                    $return = '添加上榜产品';
                                } else {
                                    $return = '添加产品';
                                }
                            } elseif ($route == "/brand/update?id=$typeId") {
                                $return = '编辑品牌内容';
                            } elseif ($route == "/brand/create") {
                                $return = '创建品牌';
                            }
                            break;
                        case "product-details":
                            if ($model->type == "3") {
                                $return = '创建评论';
                            } elseif ($model->type == "4") {
                                $return = '修改评论';
                            } elseif ($model->type == "1") {
                               if (preg_match ('/修改.*产品图 : product_img.*product_img/',$model->description)) {
                                   $return = '修改产品图片';
                               } elseif (preg_match ('/新增.*产品图 : product_img/',$model->description)) {
                                   $return = '上传产品图片';
                               } else {
                                   $return = '上传产品图片';
                               }
                            } else {
                                preg_match ('/id为(\d+)/',$model->description,$result);
                                if ($result[1]) {
                                    $linkType = ProductLink::findOne($result[1])->type;
                                }
                                
                               if ($linkType == '1') {
                                   if (preg_match ('/修/',$model->description)) {
                                       $return = '修改了淘宝链接或id';
                                   } else {
                                       $return = '添加了淘宝链接或id';
                                   }
                               } elseif ($linkType == '2') {
                                   if (preg_match ('/修/',$model->description)) {
                                       $return = '修改了京东链接或id';
                                   } else {
                                       $return = '添加了京东链接或id';
                                   }
                               } elseif ($linkType == '3') {
                                   if (preg_match ('/修/',$model->description)) {
                                       $return = '修改了亚马逊链接或id';
                                   } else {
                                       $return = '添加了亚马逊链接或id';
                                   }
                               }
                            }
                            break;  
                    }
                    return $return;
                },
                'filter' => $type == 'brand' ? false : Html::activeDropDownList($searchModel,'type',['1' => '图片','2' => '返利链接','3' => '评论'],['prompt' => '所有'])
            ],
            [
                'attribute' => 'created_at',
                'options' => ['width' => '15%'],
                'value'     => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => false
            ],
        ],
    ]); ?>
</div>
<?php
$script = <<<JS
$(function(){
     $('#start_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    });
    $('#end_at').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        'language' : 'zh-CN',
    }); 
})
JS;
$this->registerJs($script);
?>
