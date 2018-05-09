<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tms_notice_system_read".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $notice_id
 * @property string $created_at
 */
class NoticeSystemRead extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice_system_read}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'notice_id', 'created_at'], 'integer'],
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
            'notice_id' => 'Notice ID',
            'created_at' => 'Created At',
        ];
    }
}
