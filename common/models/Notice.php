<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%notice}}".
 *
 * @property string $id
 * @property integer $type
 * @property string $title
 * @property string $content
 * @property string $created_at
 */
class Notice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'created_at'], 'integer'],
            [['title', 'content'], 'string', 'max' => 255],
            [['content'], 'required'],
            [['title', 'content'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
//             'type' => 'Type',
            'title' => '通知原因',
            'content' => '通知内容',
//             'created_at' => 'Created At',
        ];
    }
}
