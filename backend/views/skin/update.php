<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use backend\models\CommonFun;
use common\models\ProductComponent;
use common\models\SkinRecommend;
use yii\grid\GridView;
use common\models\SkinRecommendProductSearch;
use common\functions\Functions;

/* @var $this yii\web\View */
/* @var $model common\models\Skin */

$this->title = '肤质编辑 ：' . $model->skin.'('.$model->explain.')';
$this->params['breadcrumbs'][] = ['label' => '肤质列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->skin.'('.$model->explain.')', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '编辑';
?>
<div class="skin-update">
    <label class="control-label" for="skin-features">肤质ID</label>&emsp;<?=$model->id ?><br><br>
    
    <label class="control-label" for="skin-features">肤质类型&emsp;</label><?=$model->skin.'('.$model->explain.')' ?><br><br>
    
    <?php $form = ActiveForm::begin();?>

    <?= $form->field($model, 'features')->textarea(['rows' => 4]) ?>
    
    <?= $form->field($model, 'elements')->textarea(['rows' => 4]) ?>
    
    <?= $form->field($model, 'star')->dropDownList(['0'=>'0星','1'=>'1星','2'=>'2星','3'=>'3星','4'=>'4星','5'=>'5星']) ?>
    
    <?php  
        echo '<br><label class="control-label" for="">分类成分相关</label>';
        foreach ($cateList as $key=>$val) {
            if ($key <= 7) {        
                
                echo "<div style = 'border:1px solid #d2d6de;width:100%;padding:10px;margin-top:20px'>";                
                
                echo '<label class="control-label" for="">'.$val['cate_name'].'</label><br><br>';

                //编辑
                $cate_id = $val['id'];
                $model2val = SkinRecommend::find()->where("skin_id = $model->id AND category_id = $cate_id")->all();
                
                $reskindata = (!empty($model2val[0]['reskin']) && $model2val[0]['category_id'] == $val['id']) ? CommonFun::getKeyValArr(new ProductComponent(), 'id', 'name',Functions::db_create_in($model2val[0]['reskin'],'id')) : '';
                $noreskindata = (!empty($model2val[0]['noreskin']) && $model2val[0]['category_id'] == $val['id']) ? CommonFun::getKeyValArr(new ProductComponent(), 'id', 'name',Functions::db_create_in($model2val[0]['noreskin'],'id')) : '';
                $reskin =  'reskin'.($key+1);   
                $noreskin =  'noreskin'.($key+1);
                $model2->$reskin = (!empty($model2val[0]['reskin']) && $model2val[0]['category_id'] == $val['id']) ? explode(',',$model2val[0]['reskin']) : '';
                $model2->$noreskin = (!empty($model2val[0]['noreskin']) && $model2val[0]['category_id'] == $val['id']) ? explode(',',$model2val[0]['noreskin']) : '';
                
                echo $form->field($model2, 'reskin'.($key+1))->widget(Select2::classname(), [
                    'options' => ['placeholder' => '请输入成分名称 ...','multiple' => true],
                    'data' => $reskindata,
                    'showToggleAll' => false,
                    'maintainOrder' => true,
                    'pluginOptions' => [
                        'placeholder' => 'Waiting...',
                        'language'=>"zh-CN",
                        'minimumInputLength'=> 1,
                        'ajax' => [
                            'url' => 'search-component',
                            'dataType' => 'json',
                            'cache' => true,
                            'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(res) { return res.text; }'),
                        'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                    ],
                ])->label('推荐成分');      
                        
                echo $form->field($model2, 'noreskin'.($key+1))->widget(Select2::classname(), [
                    'options' => ['placeholder' => '请输入成分名称 ...','multiple' => true],
                    'data' => $noreskindata,
                    'showToggleAll' => false,
                    'maintainOrder' => true,
                    'pluginOptions' => [
                        'placeholder' => 'Waiting...',
                        'language'=>"zh-CN",
                        'minimumInputLength'=> 1,
                        'ajax' => [
                            'url' => 'search-component',
                            'dataType' => 'json',
                            'cache' => true,
                            'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(res) { return res.text; }'),
                        'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                    ],
                ])->label('不推荐成分');
                
                echo $form->field($model2, 'copy')->textarea(['rows' => 4,'id'=>'','name'=>'SkinRecommend[copy][]','class' => 'form-control copy','value' => (!empty($model2val[0]['copy']) && $model2val[0]['category_id'] == $val['id']) ? $model2val[0]['copy'] : ''])->label($val['cate_name'].'方案').'<span class="copyerror" style="color:red"></span>';
                
//                 echo
//                 '<div class="skin-recommend-product-index">
//                 <p>';
//                 Html::a('Create Skin Recommend Product', ['create'], ['class' => 'btn btn-success']);
//                 echo '</p>';
                
//                 $dataProvider = $val['id'];
//                 $searchModel = '';
//                 $searchModel = new SkinRecommendProductSearch();
//                 $search = Yii::$app->request->queryParams;
//                 $search['SkinRecommendProduct']['cate_id'] = $val['id'];
//                 $search['SkinRecommendProduct']['skin_id'] = $model->id;
//                 $dataProvider = $searchModel->search($search);
                
//                 echo GridView::widget([
//                     'dataProvider' => $dataProvider,
//                     'filterModel' => $searchModel,
//                     'columns' => [
//                         ['class' => 'yii\grid\SerialColumn'],
                
//                         'skin_id',
//                         'skin_name',
//                         'cate_id',
//                         'product_id',
                
//                         ['class' => 'yii\grid\ActionColumn'],
//                     ],
//                 ]);
//                 echo '</div>';

                echo "</div>";
            }
        }
    ?>

    <div class="form-group">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success sub','style' => 'width:100px']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$script = <<<JS
//方案字符限制
$('.sub').on('click', function () { 
    var fal = 0;
    $(".copy").each(function(){ 
        if($(this).val().length > 255){
            $(this).parent().addClass('has-error');
            $(this).next().html('不能超过255个字符');
            fal = 1;
        } else {
            $(this).parent().removeClass('has-error');
            $(this).next().html('');
        }
    });   
    if(fal == 1){
        return false;
        fal = 0;
        $(':submit').removeAttr('disabled').removeClass('disabled');
    }else{
        return true;
        $(':submit').attr('disabled', true).addClass('disabled');
    }
});
$('.copy').bind('input propertychange', function(){
    var copy = $(this).val();
    if (copy.length > 255) {
        $(this).parent().addClass('has-error');
        $(this).next().html('不能超过255个字符');
    } else {
        $(this).parent().removeClass('has-error');
        $(this).next().html('');
    }
})
JS;
$this->registerJs($script);
?>