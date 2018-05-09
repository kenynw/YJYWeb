<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article_keywords}}".
 *
 * @property string $keyword
 * @property string $link
 */
class ArticleKeywords extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article_keywords}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['keyword'], 'required'],
            [['keyword'], 'string', 'max' => 90],
            [['link'], 'string', 'max' => 255],
            [['link'],'url', 'defaultScheme' => 'http','message'=>'地址不是有效格式'],
            [['keyword'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'keyword' => '关键词',
            'link' => '链接',
        ];
    }
}
