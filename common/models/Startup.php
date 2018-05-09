<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%startup}}".
 *
 * @property integer $id
 * @property string $type
 * @property string $url
 * @property string $img
 * @property integer $sort_id
 * @property string $relation_id
 * @property integer $status
 * @property string $start_time
 * @property string $end_time
 * @property string $created_at
 */
class Startup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%startup}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['img','start_time', 'end_time','title'], 'required'],
            ['url','required','when' => function($model){ return ($model->type == '0' || $model->type == '1' || $model->type == '2' || $model->type == '3' || $model->type == '4'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '0' || $('#type').val() == '1' || $('#type').val() == '2' || $('#type').val() == '3' || $('#type').val() == '4'; }"],
            [['type', 'sort_id', 'relation_id', 'status'], 'integer'],
            [['created_at','start_time', 'end_time'], 'safe'],
            [['relation_id'], 'default', 'value' => 0],
            [['title'], 'string', 'max' => 50],
            [['url', 'img'], 'string', 'max' => 255],
            [['url','start_time', 'end_time','title'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'title' => '标题',
            'type' => '类型',
            'url'     => '地址或ID',
            'img'     => '图片',
            'sort_id' => 'Sort ID',
            'relation_id' => '关联id',
            'status'  => '状态',
            'start_time' => '上架时间',
            'end_time'   => '下架时间',
            'created_at' => '创建时间',
        ];
    }
}
