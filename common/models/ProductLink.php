<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "yjy_product_link".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $type
 * @property string $url
 * @property string $link_price
 * @property integer $update_time
 */
class ProductLink extends \yii\db\ActiveRecord
{
    public $text1;
    public $text2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yjy_product_link';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'type', 'update_time','tb_goods_id','add_time','admin_id','status'], 'integer'],
            [['link_price'], 'number'],
//             [['update_time'], 'required'],
            [['url'], 'string'],
        ];
    }
    
    public function beforeSave($insert)
    {
        if (Yii::$app->request->url != '/product-details/insert-excel') {
            if (parent::beforeSave($insert)) {
                if($insert) {
                    $this->add_time = time();
                    $this->update_time = time();
                }else{
                    $this->update_time = time();
                }
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    
    public function getProductDetails()
    {
        return $this->hasOne(ProductDetails::className(), ['id' => 'product_id']);
    }
    
    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => '产品id',
            'tb_goods_id' => '关联平台id',
            'type' => '平台类型',
            'url' => '返利链接',
            'link_price' => 'Link Price',
            'status' => '转化状态',
            'admin_id' => '管理员',
            'update_time' => '编辑时间',
            'add_time' => '创建时间',
        ];
    }
}
