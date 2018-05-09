<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Comment;
use common\functions\Functions;

class CommentSearch extends Comment
{
    public function rules()
    {
        return [
//            [['id', 'first_id', 'parent_id', 'type', 'post_id', 'user_id', 'admin_id', 'user_type', 'star', 'like_num', 'is_reply', 'status', 'created_at', 'updated_at'], 'integer'],
//            [['author', 'comment'], 'safe'],
            [['user_id','type','user_type','first_id'], 'integer'],
            [['author','product_author','article_author','comment','post_id'], 'safe'],
            ['post_id','trim']
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Comment::find()
//             ->select('{{%comment}}.*,IFNULL(c2.created_at,{{%comment}}.`created_at`) AS desc_time,c2.id child_id,comment_num,c2.user_ids,comment_all')
//             ->leftJoin('(SELECT *,count(id) comment_num,group_concat(DISTINCT CAST(user_id as char)) AS user_ids,group_concat(CAST(comment as char)) AS comment_all
//                        from (SELECT id,first_id,created_at,is_reply,user_id,comment FROM `yjy_comment` WHERE status = 1 ORDER BY created_at DESC) AS a GROUP BY first_id) AS c2','c2.first_id = {{%comment}}.id')
            ->where('status = 1');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pagesize' => '10'],
            'sort' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
//             '{{%comment}}.first_id' => $this->first_id,
//             'parent_id' => $this->parent_id,
            'type' => $this->type,
//             '{{%comment}}.post_id' => $this->post_id,
            '{{%comment}}.user_id' => $this->user_id,
            'admin_id' => $this->admin_id,
            'user_type' => $this->user_type,
            'star' => $this->star,
            'like_num' => $this->like_num,
            'is_reply' => $this->is_reply,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        //评论列表针对内容
        if (!empty($params['CommentSearch']['post_id']) && !isset($params['id'])) {
            $product = ProductDetails::find()->select('id')->where("product_name like '%{$params['CommentSearch']['post_id']}%'")->asArray()->column();
            $article = Article::find()->select('id')->where("title like '%{$params['CommentSearch']['post_id']}%'")->asArray()->column();
            $video = Video::find()->select('id')->where("title like '%{$params['CommentSearch']['post_id']}%'")->asArray()->column();
            if (!empty($product) || !empty($article) || !empty($video)) {
                $idStr = Functions::db_create_in($product,'{{%comment}}.post_id');
                $query->andWhere("$idStr AND type = '1'");
                $idStr2 = Functions::db_create_in($article,'{{%comment}}.post_id');
                $query->orWhere("$idStr2 AND type = '2'");
                $idStr3 = Functions::db_create_in($article,'{{%comment}}.post_id');
                $query->orWhere("$idStr3 AND type = '3'");
            } else {
                //无数据
                $query->where('{{%comment}}.id = 0');
            }
        } else {
            //产品文章详情
             $query->andFilterWhere(['{{%comment}}.post_id' => $this->post_id]);
        }
        
        //筛选一二级评论
        if (!empty($params['CommentSearch']['comment'])) {
            if ($params['CommentSearch']['comment'] == '1') {
                $query->andWhere("first_id = 0 AND parent_id = 0");
            } else {
                $query->andWhere("parent_id != 0");
            }
        }
        
        //产品详情评论排序规则不一样
        if (Yii::$app->controller->id == 'product-details') {
            $query->orderBy('is_digest DESC,created_at DESC');
        } else {
            $query->orderBy('created_at DESC');
        }
        
//                print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
