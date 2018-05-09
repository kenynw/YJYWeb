<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Post;

/**
 * PostSearch represents the model behind the search form about `common\models\Post`.
 */
class PostSearch extends Post
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['topic_title','topic_id','user_type'], 'safe'],
            [['topic_title'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Post::find()
            ->joinWith('topic t')
            ->joinWith('user u')
            ->where("t.status = 1");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => ['attributes' => ['created_at','like_num'=>['default' => SORT_DESC],'comment_num'=>['default' => SORT_DESC]],'defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'topic_id' => $this->topic_id,
            'views_num' => $this->views_num,
            'like_num' => $this->like_num,
            'comment_num' => $this->comment_num,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 't.title', $this->topic_title]);
        
        //筛选用户类型
        if (!empty($params['PostSearch']['user_type'])) {
            if ($params['PostSearch']['user_type'] == '1') {
                $query->andWhere("u.admin_id = 0");
            } else {
                $query->andWhere("u.admin_id <> 0");
            }
        }
        
//                        print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
