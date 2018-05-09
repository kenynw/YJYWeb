<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%skin}}".
 *
 * @property string $id
 * @property string $skin
 * @property string $product_id
 */
class Skin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%skin}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['skin'], 'required'],
            [['star'],'integer'],
            [['skin','explain'], 'string', 'max' => 50],
            [['features','elements'], 'string', 'max' => 1000],
            [['features','elements'], 'trim'],
        ];
    }
    
    public function getArticle()
    {
        return $this->hasMany(Article::className(), ['skin_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'skin' => '肤质类型',
            'features' => '肤质特征',
            'elements' => '保养要素',
            'star' => '肤质护理难度'
        ];
    }
}
