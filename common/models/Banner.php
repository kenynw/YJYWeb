<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%banner}}".
 *
 * @property string $id
 * @property string $title
 * @property string $url
 * @property string $img
 * @property integer $type
 * @property integer $sort_id
 * @property string $start_time
 * @property string $end_time
 * @property string $created_at
 * @property string $updated_at
 */
class Banner extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%banner}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'sort_id', 'created_at', 'updated_at','status','relation_id','position'], 'integer'],
            [['title', 'img', 'sort_id', 'start_time', 'end_time'], 'required'],
            ['url','required','when' => function($model){ return ($model->type == '1' || $model->type == '2' ||$model->type == '3'||$model->type == '5'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '1' || $('#type').val() == '2' || $('#type').val() == '3' || $('#type').val() == '5'; }"],
            [['img'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 1000],
            [['title'], 'string', 'max' => 50],
            [['end_time'], 'time'],
            [['title','sort_id','url'], 'trim'],
            [['relation_id'], 'default', 'value' => 0],
        ];
    }

    public function time($attribute, $params)
    {
        $start = $this->start_time;
        $end = $this->end_time;

        if ($start >= $end) {
            $this->addError($attribute, "下架时间必须大于生效时间");
        }
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
                //'value'=>$this->timeTemp(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'      => 'ID',
            'title'   => '标题',
            'url'     => '地址或ID',
            'img'     => 'banner图',
            'type'    => 'banner类型',
            'status'  => '状态',
            'position' => '位置',
            'sort_id' => '排序号',
            'start_time' => '上架时间',
            'end_time'   => '下架时间',
            'created_at' => '创建时间',
            'updated_at' => '最后修改时间',
            'relation_id' => '文章或产品ID'
        ];
    }

    public static function getBannerType(){
        return \Yii::$app->params['bannerType'];
    }
}
