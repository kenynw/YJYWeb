<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_account}}".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $pay
 * @property string $content
 * @property string $money
 * @property string $created_at
 * @property string $updated_at
 * @property string $admin_name
 * @property string $remark
 */
class UserAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_account}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'money', 'created_at', 'updated_at'], 'integer'],
            [['pay', 'content', 'admin_name', 'remark'], 'string', 'max' => 255],
        ];
    }
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户名',
            'type' => '充值/消费',
            'pay' => '支付类型',
            'content' => '事项',
            'remark' => '备注',
            'money' => '分值',
            'admin_name' => '操作人',
            'created_at' => '获取时间',
        ];
    }
}
