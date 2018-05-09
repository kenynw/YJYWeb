<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%skin_recommend_product}}".
 *
 * @property integer $skin_id
 * @property string $skin_name
 * @property integer $cate_id
 * @property string $product_id
 */
class SkinRecommendProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%skin_recommend_product}}';
    }

    /**
     * @inheritdoc
     */
    public $product_name;
    public $star;
    public $has_img;
    public $price;
    public $form;
    public function rules()
    {
        return [
            [['skin_id', 'cate_id', 'product_id'], 'required'],
            [['skin_id', 'cate_id', 'product_id','status'], 'integer'],
            [['skin_name'], 'string', 'max' => 4],
            
//             [['status', 'is_recommend'], 'integer'],
//             ['is_top', 'integer'],
//             ['brand_id','default', 'value'=>'0'],
        ];
    }
    
    public function getProductDetails()
    {
        return $this->hasOne(ProductDetails::className(), ['id' => 'product_id']);
    }
    
    public function getSkin()
    {
        return $this->hasOne(Skin::className(), ['id' => 'skin_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'skin_id' => '肤质类型',
            'skin_name' => '肤质类型',
            'cate_id' => '分类',
            'product_id' => '产品名称',
            'product_name' => '产品名',
            'star' => '星级',
            'price' => '价格',
            'form' => '规格',
            'has_img' => '是否有图',                       
        ];
    }
}
