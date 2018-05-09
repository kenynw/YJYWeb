<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_relate}}".
 *
 * @property string $id
 * @property integer $product_id
 * @property integer $component_id
 */
class ProductRelate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_relate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'component_id'], 'required'],
            [['product_id', 'component_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'component_id' => 'Component ID',
        ];
    }
}