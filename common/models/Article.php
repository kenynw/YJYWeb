<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%article}}".
 *
 * @property integer $id
 * @property string $title
 * @property integer $status
 * @property integer $is_recommend
 * @property string $article_img
 * @property string $weixin_url
 * @property integer $created_at
 */
class Article extends \yii\db\ActiveRecord
{
    public $cate_ids;
    public $tag_name;
    public $new_tag;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'article_img','content','cate_ids'], 'required'],
            [['status', 'is_recommend', 'created_at','cate_id','click_num','admin_id','retime','stick'], 'integer'],
            [['article_img','title'], 'string', 'max' => 255],
            [['content','product_id','brand_id','product_cate_id'], 'safe'],
            ['skin_id','default', 'value'=>'0'],
            [['tag_name','new_tag'],'checkTagName'],
            [['title'], 'trim'],
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
    
    public function checkTagName($attribute, $params)
    {
        if (count($this->tag_name) + count(explode(',', substr($this->new_tag,0,strlen($this->new_tag)-1))) > 5) {
            $this->addError($attribute, "文章关键词最多五个");
        };
    }

    public function getArticleCategory()
    {
        return $this->hasOne(ArticleCategory::className(), ['id' => 'cate_id']);
    }
    
    public function getSkin()
    {
        return $this->hasOne(Skin::className(), ['id' => 'skin_id']);
    }
    
    public static function getParent($cate_id)
    {
        $parent_id = ArticleCategory::find()->select("parent_id")->andWhere(['id' => $cate_id])->asArray()->one();
        $info = ArticleCategory::find()->select("id,cate_name")->andWhere(['id' => $parent_id['parent_id'] ])->asArray()->one();
        return $info;
    }
    
    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id']);
    }

    //文章一级分类
    public static function getFirstClass($cate_id){
        $parent_id = ArticleCategory::find()->select("parent_id")->andWhere(['id' => $cate_id])->asArray()->scalar();
        if($parent_id){
            $result = ArticleCategory::find()->select("cate_name")->andWhere(['id' => $parent_id ])->asArray()->scalar();
        }else{
            $result = ArticleCategory::find()->select("cate_name")->andWhere(['id' => $cate_id ])->asArray()->scalar();
        }
        return $result;
    }

    //文章二级分类
    public static function getSecondClass($cate_id){
        $cateInfo = ArticleCategory::find()->select("parent_id,cate_name")->andWhere(['id' => $cate_id])->asArray()->one();
        $result = "";
        if($cateInfo['parent_id'] != 0){
            $result = $cateInfo['cate_name'];
        }

        return $result;
    }
    public static  function articleClick($id){
        //点击数+1
        $model  = Article::findOne($id);
        $time   = strtotime("-7 days");

        if(empty($model->week_click_time) || ($model->week_click_time < $time)){
            $model->week_click = 1;
            $model->week_click_time = time();
        }else{
            $model->week_click = $model->week_click + 1;
        }
        $model->click_num = $model->click_num + 1;

        $model->save(false);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '文章ID',
            'title' => '文章标题',
            'tag_name' => '关键词',
            'cate_id' => '文章分类',
            'cate_ids' => '一级分类',
            'click_num' => '阅读量',
            'status' => '状态',
            'is_recommend' => '是否推荐',
            'stick' => '是否置顶',
            'content' => '文章内容',
            'article_img' => '文章配图',
            'created_at' => '创建时间',
            'admin_id' => '创建人'
        ];
    }
}
