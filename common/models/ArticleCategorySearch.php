<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ArticleCategory;

/**
 * ArticleCategorySearch represents the model behind the search form about `common\models\ArticleCategory`.
 */
class ArticleCategorySearch extends ArticleCategory
{
    public function rules()
    {
        return [
            [['status', 'created_at'], 'integer'],
//            [['cate_name'], 'safe'],
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
        $query = ArticleCategory::find()
            ->select("{{%article_category}}.*,count(t.id) as cate_num ")
            ->joinWith('articleCategory as t')
            ->groupBy("{{%article_category}}.id")
            ->orderBy('`order`');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'created_at'
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            //'id' => $this->id,
            '{{%article_category}}.status' => $this->status,
        ]);

        //$query->andFilterWhere(['like', 'cate_name', $this->cate_name]);

        if(!empty($params['parent_id'])){
            $query->andFilterWhere(['{{%article_category}}.parent_id' => $params['parent_id']]);
        }else{
            $query->andFilterWhere(['{{%article_category}}.parent_id' => 0]);
        }

        //起始时间搜索
        if (!empty($params['start_at']) && !empty($params['end_at'])) {
            $startTime = strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%article_category}}.created_at', $startTime, $endTime]);
        }

        return $dataProvider;
    }
}
