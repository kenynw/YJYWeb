<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_inventory_product}}".
 *
 * @property string $id
 * @property string $product_id
 * @property string $invent_id
 * @property integer $order
 * @property string $desc
 * @property string $add_time
 */
class UserInventoryProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_inventory_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'invent_id', 'order'], 'integer'],
            [['add_time'], 'safe'],
            [['desc'], 'string', 'max' => 255],
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
            'product_id' => 'Product ID',
            'invent_id' => 'Invent ID',
            'order' => 'Order',
            'desc' => '备注',
            'add_time' => 'Add Time',
        ];
    }
}
