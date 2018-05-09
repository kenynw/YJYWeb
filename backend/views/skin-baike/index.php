<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Skin;
use common\functions\Functions;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SkinBaikeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '肤质百科';
$this->params['breadcrumbs'][] = $this->title;

//肤质
$skinid = Skin::find()->asArray()->all();
$skinList = [];
foreach ($skinid as $key=>$val) {
    $skinList[$val['id']] = $val['skin'].'('.$val['explain'].')';
}
?>
<div class="skin-baike-index">
    <p>
        <?= Html::a('添加百科','javascript:void(0)', ['class' => 'btn btn-success data-update','data-target'=>'#update-modal','data-toggle'=>'modal','data-id'=>'']) ?>
    </p>

    <?php
    use yii\bootstrap\Modal;
    //创建修改modal
    Modal::begin([
        'id' => 'update-modal',
        'header' => '<h4 class="modal-title">更新</h4>',
    ]);
    Modal::end();
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => ['class'=>''],
        'columns' => [
            
            [
                'attribute' => 'id',
                'options' => ['width' => '5%']
            ],
            [
                'attribute' => 'question',
                'headerOptions' => ['style' => 'white-space:pre-wrap;max-width:300px'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:300px'],
                'format' => 'raw',
                'value' => function ($model) {
                    $img = empty($model->skinBaikeAnswer->picture) ? '' : '&nbsp;'.Html::a('查看图片','javascript:void(0)',['data-url' => Functions::get_image_path($model->skinBaikeAnswer->picture),'class' => 'img','data-toggle' => 'modal','data-target' => '#img']);
                    return $model->question.$img;
                }
            ],
            [
                'attribute' => 'answer',
                'headerOptions' => ['style' => 'white-space:pre-wrap;max-width:350px'],
                'contentOptions' => ['style' => 'white-space:pre-wrap;max-width:350px'],
                'value' => function ($model) {
                    return empty($model->skinBaikeAnswer->content) ? '' : $model->skinBaikeAnswer->content;
                }
            ],
            [
                'attribute' => 'shortanswer',
                'options' => ['width' => '15%'],
                'value' => function ($model) {
                    return empty($model->skinBaikeAnswer->shortcontent) ? '' : $model->skinBaikeAnswer->shortcontent;
                }
            ],
//             [
//                 'attribute' => 'skin_id',
//                 'format' => 'raw',
//                 'value'     => function($model) use($skinList){
//                     return $skinList[$model->skin_id];
//                 },
//                 'options' => ['width' => '10%'],
//                 'filter' => Html::activeDropDownList($searchModel,
//                     'skin_id',$skinList,
//                     ['prompt' => '所有'])
//             ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}&nbsp;{delete}',
                'header' => '操作',
                'buttons' => [                 
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
                            'data-toggle' => 'modal',
                            'data-target' => '#update-modal',
                            'class' => 'data-update',
                            'data-id' => $key,
                        ]);
                    },
                ]
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
    </div>
</div>
<?php 
$script = <<<JS
//清空modal
$('#update-modal').on('hidden.bs.modal', function () {
    $("#update-modal .modal-body").empty();
})  
//创建修改modal
 $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('#update-modal .modal-title').html("添加");
        var url = '/skin-baike/create';
    } else {
        $('#update-modal .modal-title').html("编辑百科");
        var url = '/skin-baike/update';
    }
    $.get(url, { id: id },
        function (data) {
            $('#update-modal .modal-body').html(data);
        }  
    );
});

//图片弹窗
$('.img').on('click', function(){
    $('.image').attr('src', this.getAttribute("data-url"));
});

JS;
$this->registerJs($script);
?>
