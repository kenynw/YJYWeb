<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%admin_log}}".
 *
 * @property integer $id
 * @property string $route
 * @property string $description
 * @property integer $created_at
 * @property integer $user_id
 */
class AdminLog extends \yii\db\ActiveRecord
{
    public $type;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description','username','route'], 'string'],
            [['created_at'], 'required'],
            [['created_at', 'user_id', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'route' => '路由',
            'description' => '内容',
            'created_at' => '时间',
            'username' => '管理员',
        ];
    }
}
