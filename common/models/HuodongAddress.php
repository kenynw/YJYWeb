<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%huodong_address}}".
 *
 * @property string $id
 * @property integer $hid
 * @property string $user_id
 * @property string $name
 * @property string $tel
 * @property string $address
 * @property string $add_time
 */
class HuodongAddress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%huodong_address}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hid', 'user_id'], 'integer'],
            [['add_time'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['tel'], 'string', 'max' => 15],
            [['address'], 'string', 'max' => 250],
        ];
    }
    
    public function getHuodongSpecialConfig()
    {
        return $this->hasOne(HuodongSpecialConfig::className(), ['id' => 'hid']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hid' => '活动名称',
            'user_id' => '用户ID',
            'name' => '名字',
            'tel' => '电话',
            'address' => '地址',
            'add_time' => '添加时间',
        ];
    }
}
