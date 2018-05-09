<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "tms_admin".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Admin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yjy_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password_hash', 'created_at', 'updated_at'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    
    public static function getAccess()
    {      
        $access = yii\helpers\ArrayHelper::map((new \yii\db\Query())
        ->select('parent')
        ->from('{{%auth_item_child}}')
        ->groupBy('parent')
        ->all(), 'parent', 'parent');
        
        return $access;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '管理员ID',
            'username' => '用户名',
            'status' => '账号状态',
            'password_hash' => '密码',
            'connect_user_id' => '关联账号id',
            'connect_user_username' => '关联账号用户名',
            'created_at' => '创建时间',
            'updated_at' => '最后修改时间',
        ];
    }
}
