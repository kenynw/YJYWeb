<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%product_bonus}}".
 *
 * @property string $id
 * @property string $goods_id
 * @property integer $website_type
 * @property string $goods_link
 * @property string $bonus_link
 * @property string $price
 * @property string $start_date
 * @property string $end_date
 * @property integer $status
 * @property integer $created_at
 * @property string $update_at
 */
class ProductBonus extends \yii\db\ActiveRecord
{
    public $type;
    public $data_type;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_bonus}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['goods_id','goods_link','status','bonus_link','price','start_date', 'end_date'],'required'],
            [['goods_id', 'website_type', 'status', 'created_at', 'updated_at','type','product_id','sort','data_type','is_off','is_manual'], 'integer'],
            [['price'], 'number'],
            [['start_date', 'end_date'], 'safe'],
            [['goods_link', 'bonus_link'], 'string'],
            [['goods_link','goods_id'],'required','when' => function($model){ return ($model->type == '1'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '1'; }"],
            [['bonus_link','start_date', 'end_date','price','goods_link','goods_id'],'required','when' => function($model){ return ($model->type == '0'); },'whenClient' => "function (attribute, value) { return $('#type').val() == '0'; }"],
            [['goods_link', 'bonus_link'],'url', 'defaultScheme' => 'http','message'=>'地址不是有效格式'],
            [['bonus_link','start_date', 'end_date','price','goods_id','goods_link'],'trim'],
            [['price','sort'],'default', 'value'=>'0'],
            [['goods_id'],'unique','message'=>'该商品id已存在'],
        ];
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }
    
    public function getProductDetails()
    {
        return $this->hasOne(ProductDetails::className(), ['id' => 'product_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '淘宝商品id',
            'website_type' => 'Website Type',
            'goods_link' => '商品链接',
            'bonus_link' => '优惠券链接',
            'price' => '优惠券价格',
            'start_date' => '开始时间（优惠券有效期）',
            'end_date' => '结束时间（优惠券有效期）',
            'status' => '状态',
            'created_at' => 'Created At',
            'updated_at' => '编辑时间',
            'type' => '链接类型',
            'product_id' => '产品id',
            'data_type' => '数据类型',
            'sort' => '排序'
        ];
    }
}
