<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Article;

/**
 * ArticleSearch represents the model behind the search form about `common\models\Article`.
 */
class ArticleSearch extends Article
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'is_recommend', 'created_at','cate_id','cate_ids','skin_id','click_num'], 'integer'],
            [['title', 'article_img'], 'safe'],
            [['title'], 'trim'],
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
        $query = Article::find()->orderBy('stick DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => isset($params["pages"]) ? intval($params["pages"]) : '20',
            ],
            'sort' => [
                'attributes' =>['created_at','click_num'],
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ]
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
            'cate_id' => $this->cate_id,
            'status' => $this->status,
            'is_recommend' => $this->is_recommend,
            'skin_id' => $this->skin_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'article_img', $this->article_img]);

        //2级分类搜索
        if(isset($params['ArticleSearch']['cate_ids']) && !empty($params['ArticleSearch']['cate_ids'])){
            $list = ArticleCategory::find()->select("id")->andWhere(['parent_id' => $params['ArticleSearch']['cate_ids']])->asArray()->column();
            if($list){
                $cond = ['or', ['cate_id' => $params['ArticleSearch']['cate_ids'] ], ['cate_id' => $list ]];
                $query->andFilterWhere($cond);
            }else{
                $query->andFilterWhere(['cate_id'=>$params['ArticleSearch']['cate_ids'] ]);
            }
        }

        return $dataProvider;
    }
}
