<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article_category}}".
 *
 * @property integer $id
 * @property string $cate_name
 * @property integer $status
 * @property integer $created_at
 */
class ArticleCategory extends \yii\db\ActiveRecord
{
    public $article_num;
    public $cate_num;
    public static function tableName()
    {
        return '{{%article_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cate_name'], 'required'],
            [['status', 'created_at','parent_id','order'], 'integer'],
            ['order','default','value'=>0],
            [['cate_name'], 'string', 'max' => 50],
            [['describe','cate_img'], 'string', 'max' => 255],
            [['cate_name','order','describe'], 'trim'],
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

    public function getArticle()
    {
        return $this->hasMany(Article::className(), ['cate_id' => 'id']);
    }

    public function getArticleCategory()
    {
        return $this->hasMany(ArticleCategory::className(), ['parent_id' => 'id']);
    }

    //获取子分类名称
    static function getChildCateName($id){
        $row = ArticleCategory::find()->select("cate_name")->andWhere(['parent_id' => $id])->asArray()->column();
        return $row;
    }


    //获取分类下的文章数量
    static function getArticleNum($id,$parent_id){
        if($parent_id == 0){
            $child_ids = ArticleCategory::find()->select("id")->andWhere(['parent_id' => $id])->asArray()->column();
            if($child_ids){
                $cond = ['or', ['cate_id' => $id ], ['cate_id' => $child_ids ]];
            }else{
                //只有一级
                $cond = ['cate_id' => $id];
            }
        }else{
            $cond = ['cate_id' => $id];
        }
        $num = Article::find()->andWhere($cond)->count('{{%article}}.id');

        return $num;
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cate_name' => '分类名',
            'article_num' => '文章数',
            'status' => '状态',
            'order' => '排序',
            'describe' => '描述',
            'cate_img' => '图片',
            'created_at' => '创建时间',
        ];
    }
}
