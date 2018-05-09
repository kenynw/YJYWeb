<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ReportApp;

/**
 * ReportAppSearch represents the model behind the search form about `common\models\ReportApp`.
 */
class ReportAppSearch extends ReportApp
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['id', 'register_num', 'banner_click', 'banner_click_num', 'lessons_num', 'evaluating_num', 'article_num', 'product_num', 'date'], 'integer'],
            [['referer'], 'safe'],
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
        $query = ReportApp::find()
            ->select("SUM(register_num) AS register_num,SUM(banner_click) AS banner_click,SUM(banner_click_num) AS banner_click_num,SUM(lessons_num) AS lessons_num,SUM(evaluating_num) AS evaluating_num,SUM(article_num) AS article_num,SUM(product_num) AS product_num,date,referer")
            ->groupBy("referer");
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        if(!empty($params['start_at']) && !empty($params['end_at'])){
            $start_at = strtotime($params['start_at']);
            $end_at =  strtotime($params['end_at']);
            $query->andFilterWhere(['between', 'date', $start_at, $end_at]);
        }else{
            //默认显示最近7天
             $start_at = date('Y-m-d',strtotime('-7 days'));
             $start_at = strtotime($start_at);
        
             $end_at = date('Y-m-d',time());
             $end_at =  strtotime($end_at) + 86399;
        
             $query->andFilterWhere(['between', 'date', $start_at, $end_at]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
//             'id' => $this->id,
//             'register_num' => $this->register_num,
//             'banner_click' => $this->banner_click,
//             'banner_click_num' => $this->banner_click_num,
//             'lessons_num' => $this->lessons_num,
//             'evaluating_num' => $this->evaluating_num,
//             'article_num' => $this->article_num,
//             'product_num' => $this->product_num,
//             'date' => $this->date,
        ]);

        $query->andFilterWhere(['like', 'referer', $this->referer]);

        return $dataProvider;
    }
}
