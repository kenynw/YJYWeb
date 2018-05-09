<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%headline}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $created_at
 * @property integer $updated_at
 */
class Headline extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%headline}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'title', 'content', 'created_at', 'updated_at'], 'required'],
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'content'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
