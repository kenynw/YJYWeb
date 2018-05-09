<?php

namespace common\models;

use Yii;
use backend\models\CommonFun;
use yii\base\Object;

/**
 * This is the model class for table "{{%user_inventory}}".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $title
 * @property string $picture
 * @property integer $status
 * @property string $add_time
 */
class UserInventory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_inventory}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status'], 'integer'],
            [['add_time'], 'safe'],
            [['title', 'picture'], 'string', 'max' => 255],
        ];
    }
    
    public function getProductDetails()
    {
        return $this->hasOne(ProductDetails::className(), ['id' => 'product_id']);
    }
    
    public static function getProduct($id)
    {
        $productArr = UserInventoryProduct::find()->select("product_id")->where("invent_id = $id")->asArray()->column();
        if (!empty($productArr)) {
            $productArr = CommonFun::getConnectArr($productArr, new ProductDetails(), 'id', 'product_name');
        }
        
        return $productArr;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => '清单名',
            'picture' => 'Picture',
            'status' => 'Status',
            'add_time' => '创建时间',
        ];
    }
}
