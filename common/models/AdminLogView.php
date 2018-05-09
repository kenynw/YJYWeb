<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%admin_log_view}}".
 *
 * @property integer $id
 * @property string $route
 * @property integer $user_id
 * @property string $username
 * @property integer $log_id
 * @property string $description
 * @property integer $created_at
 */
class AdminLogView extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_log_view}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'relate_id', 'created_at'], 'integer'],
            [['username', 'relate_id', 'created_at'], 'required'],
            [['description'], 'string'],
            [['route', 'username'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'route' => 'Route',
            'user_id' => 'User ID',
            'username' => '管理员',
            'log_id' => 'Log ID',
            'description' => '内容',
            'created_at' => '时间',
        ];
    }
}
