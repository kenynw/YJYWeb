<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Admin;
use yii\bootstrap\Modal;
use common\functions\Tools;
use common\models\User;

$date1 = yii::$app->getRequest()->get('start_at');
$date2 = yii::$app->getRequest()->get('end_at');

?>

<div class="user-index">
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="glyphicon glyphicon-plus"></i> 创建马甲','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal']) ?>
    </p>

    <?php
    //创建马甲modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">创建马甲</h4>',
        //'size' => "modal-lg",
    ]);
    Modal::end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'username',
                'options' => ['width' => '12%'],
                'format' => 'raw',
                'value' => function($model){
                    if($model->admin_id == 0){
                        $style = "color:green";
                        $title = "用户";
                        return Html::a(Tools::userTextDecode($model->username), ['user/view','id'=> $model->id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']);
                    }else{
                        $style = "color:blue";
                        $title = "马甲";
                        return Html::a(Tools::userTextDecode($model->username), ['user/view','id'=> $model->id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']);
                    }
                },
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:100px'],
            ],
            [
                'attribute' => 'mobile',
                'options' => ['width' => '8%'],
                'value' => function($model) {
                    return ((substr($model->mobile,0,1) == 'w') || substr($model->mobile,0,1) == 's') ? '' : $model->mobile;
                },
            ],
            [
                'label' => '类型',
                'value' => function($model){
                    return "";
                },
                'options' => ['width' => '5%'],
                'filter' => Html::activeDropDownList($searchModel,
                    'userType', ['1' => '用户', '2'=> '马甲'],
                    ['prompt'=>'所有']
                ),
            ],
            [
                'attribute' => 'referer',
                'format' => 'raw',
                'value' => function($model){
                    return $model->referer;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'referer', ['H5' => 'H5','Android' => 'Android','IOS' => 'IOS'],
                    ['prompt'=>'所有']
                ),
                'options' => ['width' => '5%']
            ],
            [
                'attribute' => 'user_product_num',
                'value' => function ($model) {
                    return empty($model->user_product_num) ? '-' : $model->user_product_num;
                },
            ],
            [
                'attribute' => 'user_inventory_num',
                'value' => function ($model) {
                    return empty($model->user_inventory_num) ? '-' : $model->user_inventory_num;
                },
            ],
            [
                'attribute' => 'product_comment_num',
                'value' => function ($model) {
                    return empty($model->product_comment_num) ? '-' : $model->product_comment_num;
                },
            ],

            [
                'attribute' => 'acticle_comment_num',
                'value' => function ($model) {
                    return empty($model->acticle_comment_num) ? '-' : $model->acticle_comment_num;
                },
            ],
            [
                'attribute' => 'askreply_num',
                'value' => function ($model) {
                    return empty($model->askreply_num) ? '-' : $model->askreply_num;
                },
            ],
            [
                'attribute' => 'feedback_num',
                'value' => function ($model) {
                    return empty($model->feedback_num) ? '-' : $model->feedback_num;
                },
            ],
            [
                'label' => '超级账号',
                'value' => function($model){
                    return empty($model->admin->username) ? '' : $model->admin->username;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'admin_id', \yii\helpers\ArrayHelper::map(Admin::find()->all(),'id','username'),
                    ['prompt'=>'所有']
                ),
                'options' => ['width' => '8%'],
            ],
            [
                'attribute' => 'status',
                'value' => function($model){
                    if($model->status == 1){
                        return "正常";
                    }else if($model->status == 2){
                        return "禁言";
                    }else if($model->status == 3){
                        return "封号";
                    }
                },
                'options' => ['width' => '8%'],
                'filter' => Html::activeDropDownList($searchModel,
                    'status', ['1' => '正常', '2'=> '禁言', '3'=> '封号'],
                    ['prompt'=>'所有']
                ),
            ],


            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'filter' => Html::input('text', 'start_at', $date1, ['class' => 'required','id' => 'start_at','style' => 'width:80px;']) ." -- ".Html::input('text', 'end_at', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at','style' => 'width:80px;']),
                'options' => ['width' => '13.5%'],
            ],
        ],
        'pager' => [
            'firstPageLabel' => "首页",
            'prevPageLabel' => '上一页',
            'nextPageLabel' => '下一页',
            'lastPageLabel' => '最后一页',
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

//清空modal
$('.modal').on('hidden.bs.modal', function () {
    $(".modal-body").empty();
})  
//创建修改modal
$('.data-update').on('click', function () {
    $.get('/user/create', {},
        function (data) {
            $('.modal-body').html(data);
        }  
    );
});

JS;
$this->registerJs($script);
?>
