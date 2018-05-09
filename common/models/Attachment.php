<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%attachment}}".
 *
 * @property string $aid
 * @property string $cid
 * @property string $uid
 * @property string $attachment
 * @property string $description
 * @property string $dateline
 */
class Attachment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%attachment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'uid', 'dateline'], 'integer'],
            [['ratio'], 'number'],
//             [['description'], 'required'],
            [['attachment', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'aid' => 'Aid',
            'cid' => 'Cid',
            'uid' => 'Uid',
            'attachment' => 'Attachment',
            'description' => 'Description',
            'dateline' => 'Dateline',
        ];
    }
}
