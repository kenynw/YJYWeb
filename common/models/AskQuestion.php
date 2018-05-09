<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%ask_question}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $question
 * @property integer $order
 * @property integer $add_time
 */
class AskQuestion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ask_question}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'order', 'add_time'], 'integer'],
            [['question'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'question' => 'Question',
            'order' => 'Order',
            'add_time' => 'Add Time',
        ];
    }
}
