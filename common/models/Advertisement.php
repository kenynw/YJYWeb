<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%advertisement}}".
 *
 * @property string $id
 * @property string $title
 * @property string $type
 * @property integer $status
 * @property string $position
 * @property string $url
 * @property string $img
 * @property string $desc
 * @property integer $created_at
 */
class Advertisement extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%advertisement}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'type', 'url', 'img','start_time','end_time'], 'required'],
            [['status', 'created_at','sort'], 'integer'],
            [['title', 'url', 'img', ], 'string', 'max' => 100],
            [['type', 'position'], 'string', 'max' => 50],
            [['title','url'], 'trim'],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->created_at = time();
            }
            return true;
        } else {
            return false;
        }
    }

    static function getType()
    {
        $typeList = [
            'site/index' => "首页",
            'product/index' => "产品库页",
            'product/details' => "产品详情页",
            'article/index' => "文章库页",
            'article/details' => "文章详情页",
            'brand/index' => "品牌库页",
            'brand/details' => "品牌详情页",
            'component/index' => "成分详情页",
        ];
        return $typeList;
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'type' => '投放页面',
            'status' => '是否上架',
            'position' => '投放位置',
            'url' => '跳转url',
            'img' => '广告图',
            'start_time' => '上架时间',
            'end_time'   => '下架时间',
            'created_at' => '创建时间',
            'sort' => '投放次序',
        ];
    }
}
