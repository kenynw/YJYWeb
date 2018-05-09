<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_product}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $brand_id
 * @property string $brand_name
 * @property string $product
 * @property string $img
 * @property integer $is_seal
 * @property string $seal_time
 * @property integer $quality_time
 * @property string $overdue_time
 * @property integer $days
 * @property string $expire_time
 * @property string $add_time
 */
class UserProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'brand_id', 'is_seal', 'seal_time', 'quality_time', 'overdue_time', 'days', 'expire_time', 'add_time','days'], 'integer'],
            [['brand_id'], 'required'],
            [['brand_name', 'product', 'img'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'brand_id' => 'Brand ID',
            'brand_name' => '品牌',
            'product' => '产品名称',
            'img' => 'Img',
            'is_seal' => '状态',
            'seal_time' => '开封时间',
            'quality_time' => '开封保质期',
            'overdue_time' => '过期时间',
            'days' => 'Days',
            'expire_time' => 'Expire Time',
            'add_time' => '创建时间',
        ];
    }
}
