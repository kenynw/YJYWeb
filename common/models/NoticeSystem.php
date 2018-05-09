<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tms_notice_system".
 *
 * @property string $id
 * @property string $admin_id
 * @property string $title
 * @property string $content
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class NoticeSystem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice_system}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'status', 'created_at', 'updated_at','type'], 'integer'],
            [['title', 'content'], 'string', 'max' => 255],
            ['relation','required','when' => function($model){ return ($model->type == '2' || $model->type == '3' ||$model->type == '4'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '2' || $('#type').val() == '3' || $('#type').val() == '4'; }"],
            ['relation','url', 'defaultScheme' => 'http','message'=>'地址不是有效格式','when' => function($model){ return ($model->type == '2'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '2'; }"],
            ['relation','integer','message'=>'ID必须是整数','when' => function($model){ return ($model->type == '3' ||$model->type == '4'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '3' || $('#type').val() == '4'; }"],
            [['content'], 'required'],
            [['title', 'content'], 'trim'],
            
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => '管理员id',
            'title' => '标题',
            'content' => '通知内容',
            'status' => '状态',
            'type' => '通知类型',
            'relation' => '地址或ID',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
