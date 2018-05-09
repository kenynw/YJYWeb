<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%skin_baike_answer}}".
 *
 * @property integer $id
 * @property integer $qid
 * @property string $content
 */
class SkinBaikeAnswer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%skin_baike_answer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['qid'], 'required'],
            [['qid'], 'integer'],
            [['content'], 'string'],
            [['shortcontent','picture'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qid' => 'Qid',
            'content' => 'Content',
        ];
    }
}
