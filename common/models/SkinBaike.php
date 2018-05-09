<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%skin_baike}}".
 *
 * @property integer $id
 * @property integer $skin_id
 * @property string $skin_name
 * @property string $question
 * @property integer $order
 * @property string $add_time
 */
class SkinBaike extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%skin_baike}}';
    }

    /**
     * @inheritdoc
     */
    public $answer;
    public $picture;
    public $shortanswer;
    public function rules()
    {
        return [
            [['skin_id', 'order', 'add_time'], 'integer'],
            [['skin_name'], 'string', 'max' => 8],
            [['question','answer'], 'required'],
            [['question','shortanswer','picture'], 'string', 'max' => 255],
            [['answer'], 'string'],
            [['question','answer','shortanswer'], 'trim'],
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->add_time = time();
                $this->update_time = time();
            } else {
                $this->update_time = time();
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function getSkinBaikeAnswer()
    {
        return $this->hasOne(SkinBaikeAnswer::className(), ['qid' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'skin_id' => '肤质类型',
            'skin_name' => 'Skin Name',
            'question' => '问题',
            'answer' => '答案',
            'shortanswer' => '简短答案',
            'order' => 'Order',
            'add_time' => 'Add Time',
            'picture' => '图片'
        ];
    }
}
