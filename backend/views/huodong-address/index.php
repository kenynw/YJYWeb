<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\HuodongAddress;
use kartik\select2\Select2;
use common\models\HuodongSpecialDraw;

/* @var $this yii\web\View */
/* @var $searchModel common\models\HuodongAddressSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '中奖地址管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="huodong-address-index">
    <p>
        <?php echo Html::button('获取最新数据', ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '5%'],
            ],

            [
                'attribute' => 'hid',
                'options' => ['width' => '25%'],
                'value'     => function($model){
                    return empty($model->huodongSpecialConfig->name) ? '' : $model->huodongSpecialConfig->name;
                },
                'filter' => Select2::widget([
                    'name' => 'HuodongAddressSearch[hid]',
                    'data' => HuodongSpecialDraw::getHuodongNameArr(),
                    'options' => ['placeholder' => '请选择...'],
                    'initValueText' => $searchModel->hid,
                    'value' => $searchModel->hid,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
//             'user_id',
            'name',
            'tel',
            'address',
            [
                'label' =>  '活动发起时间',
                'value' => function ($model) {
                    return HuodongAddress::getInviteTime($model->user_id,$model->hid)['min'];
                }
            ],
                [
                'label' =>  '结束时间',
                'value' => function ($model) {
                    return HuodongAddress::getInviteTime($model->user_id,$model->hid)['max'];
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>

<?php
$script = <<<JS
$(function(){
    $(".btn-success").click(function(){
        window.location.reload();
    })        
})
JS;
$this->registerJs($script);
?>