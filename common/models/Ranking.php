<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%ranking}}".
 *
 * @property integer $id
 * @property string $title
 * @property integer $category_id
 * @property integer $brand_id
 * @property string $banner
 * @property string $add_time
 */
class Ranking extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ranking}}';
    }

    /**
     * @inheritdoc
     */
    public $product;
    public function rules()
    {
        return [
            [['category_id', 'brand_id', 'add_time','status'], 'integer'],
            [['title', 'banner'], 'string', 'max' => 255],
            [['title', /*'banner','category_id'*/], 'required'],
            [['product'],'safe'],
            [['title'], 'trim'],
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
    
    static public function getProductNum($id)
    {
        $productNum = (new \yii\db\Query())
            ->select('*')
            ->from('{{%ranking_list}}')
            ->where("ranking_id = $id")
            ->count();
        
        return $productNum;
    }
    
    static public function getProductStr($id)
    {
        $product =  RankingList::find()
            ->select('product_id,order,p.id,p.product_name')
            ->where("ranking_id = $id")
            ->orderBy('order')
            ->joinWith('productDetails p')
            ->asArray()
            ->all();
        $productStr = "";
        foreach ($product as $key=>$val) {
            $productStr .= $val['order'].".&nbsp;";
            $productStr .= $val['product_name'];
            $productStr .= "<br>";
        }

        return $productStr;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'category_id' => '分类',
            'brand_id' => 'Brand ID',
            'banner' => '图片',
            'status' => '状态',
            'add_time' => 'Add Time',
        ];
    }
}
