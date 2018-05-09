<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_ban}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $expiration_time
 * @property string $add_time
 */
class UserBan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_ban}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'expiration_time', 'add_time'], 'integer'],
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
            'expiration_time' => 'Expiration Time',
            'add_time' => 'Add Time',
        ];
    }
}
