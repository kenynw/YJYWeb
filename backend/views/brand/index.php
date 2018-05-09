<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\CommonFun;
use yii\base\Object;
use common\models\Brand;
use common\models\BrandCategory;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel common\models\BrandSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//搜索后展示时间
$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');

$this->title = '品牌列表';

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::a('添加品牌', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'header' => '序号',
                'options' => ['width' => '2%'],
            ],

            [
                'attribute' => 'id',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'options' => ['width' => '10%'],
                'value' => function($model) {
                    return empty($model->name) ? '' : Html::a($model->name, ['view','id'=> $model->id]);
                }
            ],
            [
                'attribute' => 'ename',
                'format' => 'raw',
                'options' => ['width' => '10%'],
                'value' => function($model) {
                    return empty($model->ename) ? '' : Html::a($model->ename, ['view','id'=> $model->id]);
                }
            ],
            [
                'label' => '上榜产品数',
                'format' => 'raw',
                'options' => ['width' => '8%'],
                'value' => function ($model) {
                    return Html::a(Brand::getProduct($model->id,'2'), ['product-details/index','ProductDetailsSearch[brand_id]'=> $model->id,'ProductDetailsSearch[is_top]'=> 1,'btype'=>'brand']);
                }
            ],
            [
                'attribute' => 'product_num',
                'label' => "产品数 (".$productNum.")",
                'format' => 'raw',
                'options' => ['width' => '5%'],
                'value' => function ($model) {
                    $product_num = empty($model->product_num) ? '0' : $model->product_num;
                    return Html::a($product_num, ['product-details/index','ProductDetailsSearch[brand_id]'=> $model->id,'btype'=>'brand']);
                }
            ],
            [
                'attribute' => 'cate_id',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    $arr = CommonFun::getKeyValArr(new BrandCategory(), 'id', 'name');
                    return empty($arr[$model->cate_id]) ? '' : $arr[$model->cate_id];
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'cate_id',CommonFun::getKeyValArr(new BrandCategory(), 'id', 'name'),
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'parent_id',
                'options' => ['width' => '10%'],
                'value'     => function($model){
                    return empty($model->brand->name) ? '' : $model->brand->name;
                },
                'filter' => Select2::widget([
                    'name' => 'BrandSearch[parent_id]',
                    'data' => CommonFun::getKeyValArr(new Brand(), 'id', 'name','parent_id = 0'),
                    'options' => ['placeholder' => '请选择...'],
                    'initValueText' => $searchModel->parent_id,
                    'value' => $searchModel->parent_id,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            [
                'attribute' => 'is_recommend',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->is_recommend == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='is_recommend' data-id='".$model->id."'>已推荐</button>";
                    }else{
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='is_recommend' data-id='".$model->id."'>默认</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_recommend',['0' => '默认','1' => '已推荐'],
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'status',
                'options' => ['width' => '8%'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->status == 1){
                        return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='status' data-id='".$model->id."'>已上架</button>";
                    }else{
                        return "<button class='btn btn-xs btnstatus' data-status='0' data-type='status' data-id='".$model->id."'>已下架</button>";
                    }
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'status',['0' => '已下架','1' => '已上架'],
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'is_link',
                'options' => ['width' => '5%'],
                'format' => 'raw',
                'value' => function($model){
                    return empty($model->link_tb) && empty($model->link_jd) ? '无' : '有';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_link',['1' => '-','2' => '有'],
                ['prompt' => '所有'])
            ],
            [
                'attribute' => 'hot',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'options' => ['width' => '100px'],
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
//                 'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ."--".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;'])
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'options' => ['width' => '5%'],
                'template' => '{update} {delete}',
                'header' => '操作',
            ],
        ],
    ]); ?>
</div>

<?php 
$script = <<<JS
//ajax修改页面状态  
status_ajax("/brand/change-status");
    
$(function(){
    window.setTimeout(function(){
        $(".close").click();
    },2500);
});

JS;
$this->registerJs($script);
