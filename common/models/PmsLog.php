<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "yjy_pms_log".
 *
 * @property string $id
 * @property integer $type
 * @property integer $relation_id
 * @property integer $from_id
 * @property integer $receive_id
 * @property string $message
 * @property integer $status
 * @property string $created_at
 */
class PmsLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yjy_pms_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'relation_id', 'from_id', 'receive_id', 'status', 'created_at'], 'integer'],
            [['message'], 'string', 'max' => 255],
            [['receive_id'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'relation_id' => 'Relation ID',
            'from_id' => 'From ID',
            'receive_id' => 'Receive ID',
            'message' => 'Message',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
}
