<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\functions\Tools;
use yii\bootstrap\Modal;
use common\models\UserFeedbackSearch;
use common\models\UserFeedback;
use common\functions\Functions;
use common\models\Pms;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserFeedbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if (Yii::$app->controller->id == 'user-feedback') {
    $this->title = '用户意见列表';
}
$this->params['breadcrumbs'][] = $this->title;

//用户详情反馈列表
if (!empty($user_id)) {
    $search = Yii::$app->request->queryParams;
    $search['UserFeedbackSearch']['user_id'] = $user_id;
    $searchModel = new UserFeedbackSearch();
    $dataProvider = $searchModel->search($search);
    
    //回复用户反馈记录
    $userFeedback = Pms::find()->where("type = 4 AND receive_id = $user_id")->all();
}
?>
<?php 
//创建反馈
Modal::begin([
    'id' => 'feedback-create',
    'header' => '<h4 class="modal-title">回复反馈</h4>',
]);
Modal::end();
?>

<div class="user-feedback-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class' => ''],
        "id" => 'grid4',
        "rowOptions" => function ($model, $key, $index, $grid) {
            return $model->is_feedback == 0 ? '' : ['style' => 'background-color:#f4f4f4'];
        },
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'user_id',
                'options' => ['width' => '5%'],
                'filter' => Yii::$app->controller->id == 'user-feedback' ? true : false,
            ],
            [
                'attribute' => 'username',
                'options' => ['width' => '15%'],
                'format' => 'raw',
                'value' => function($model){
                    if(!empty($model->user) && $model->user->admin_id == 0){
                        $style = "color:green";
                        $title = "用户";
                        return Html::a(Tools::userTextDecode($model->username), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']);
                    }else{
                        $style = "color:blue";
                        $title = "马甲";
                        return Html::a(Tools::userTextDecode($model->username), ['user/view','id'=> $model->user_id], ['style' => $style,'title' => $title,'data-toggle'=>'tooltip','data-placement'=>'right']);
                    }
                },
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:100px'],
                'visible' => Yii::$app->controller->id == 'user-feedback' ? '1' : '0',
            ],
            [
                'attribute' => 'source',
                'format' => 'raw',
                'value' => function($model){
                    return $model->source == 1 ? 'android' : 'ios';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'source', ['1' => 'android', '2' => 'ios'],
                    ['prompt' => '所有']
                ),
                'options' => ['width' => '8%'],
            ],
            [
                'attribute' => 'number',
                'format' => 'raw',
                'value' => function($model){
                    return $model->number;
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'number', UserFeedback::getNumber(),
                    ['prompt' => '所有']
                ),
                'options' => ['width' => '8%'],
            ],
            [
                'attribute' => '设备信息',
                'options' => ['width' => '10%'],
                'value' => function ($model) {
                    return $model->model.$model->system;
                }
            ],
            [
                'attribute' => 'content',
                'options' => ['width' => '22%'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:40%;word-break:break-all'],
                'format' => 'raw',
                'value' => function ($model) {
                    if (preg_match ('/http:\/\/oss.*"/',$model->content,$result)) {
                        $src = substr($result['0'], 0, -1);
                        return Html::a('查看图片','javascript:void(0)',['data-url' => $src,'class' => 'img','data-toggle' => 'modal','data-target' => '#img']);
                    } else {
                        $attachment = empty($model->attachment) ? '' : '&nbsp;'.Html::a('查看图片','javascript:void(0)',['data-url' => Functions::get_image_path($model->attachment),'class' => 'img','data-toggle' => 'modal','data-target' => '#img']);
                        return Tools::userTextDecode($model->content).$attachment;
                    }
                }
            ],
//              'telphone',
            [
                'attribute' => 'created_at',
                'format' => 'raw',
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'options' => ['width' => '12%'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => '{create}&nbsp;&nbsp;{delete}',
                'options' => ['width' => '150px'],
                'buttons' => [                 
                    'create' => function ($url, $model, $key) {
                        $return = Yii::$app->controller->id == 'user-feedback' ? 
                        Html::a('回复','#',[
                            'data' => [
                                'target' => '#feedback-create',
                                'toggle' => 'modal',
                                'id' => $model->user_id,
                            ],
                            'title' => '回复',
                            'class' => 'btn btn-default btn-xs create'
                         ]) : '';
                         return $return;
                    }, 
                    'record' => function ($url, $model, $key) {
                        Html::a('',"/user-feedback/view?id=$model->id",[
                            'class' => 'glyphicon glyphicon-file',
                            'title' => '查看回复记录'
                        ]);
                        return $return;
                    },
                    'delete' => function ($url, $model, $key) {
                        $options = [
                            'title' => Yii::t('yii', '删除'),
                            'data-method' => 'post',
                            'data-confirm' => '您确定要删除此项吗？',
                            'data-pjax' => '0'
                        ]; 
                        $return = Yii::$app->controller->id == 'user-feedback' ? Html::a('<span class="glyphicon glyphicon-trash" target=""></span>',['user-feedback/delete','id' => $model->user_id,'url' => Yii::$app->request->url],$options) : '';
                        return $return;
                    },
                ],
            ],
        ],
    ]); ?>
</div>

<!-- 图片弹窗 -->
<div class="modal fade" id="img">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">图片详情</h4>
            </div>
            <div class="modal-body" style="text-align:center">
                <img src="" class="image" class="kv-preview-data file-preview-image file-zoom-detail" style="width: 50%; height: 50%;">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<?php 
$script = <<<JS
//创建修改modal
$(document).on("click",'#grid4 .create',function(){
    var id = $(this).attr("data-id");
    $.get('/user-feedback/create', {id: id},
        function (data) {
            $('#feedback-create .modal-body').html(data);
        }  
    );
    
});

//图片弹窗
$('#grid4 .img').on('click', function(){
    $('.image').attr('src', this.getAttribute("data-url"));
});
//清空modal
$('.modal').on('hidden.bs.modal', function () {
    window.location.reload();
})  
JS;
$this->registerJs($script);
?>


