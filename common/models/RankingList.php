<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%ranking_list}}".
 *
 * @property string $id
 * @property string $product_id
 * @property integer $ranking_id
 * @property integer $order
 */
class RankingList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ranking_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'ranking_id', 'order'], 'integer'],
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
            'ranking_id' => 'Ranking ID',
            'order' => 'Order',
        ];
    }
}
