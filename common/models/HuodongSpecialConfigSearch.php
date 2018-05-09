<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\HuodongSpecialConfig;

/**
 * HuodongSpecialConfigSearch represents the model behind the search form about `common\models\HuodongSpecialConfig`.
 */
class HuodongSpecialConfigSearch extends HuodongSpecialConfig
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status','type'], 'integer'],
            [['name', 'prize', 'performer'], 'safe'],
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
        $query = HuodongSpecialConfig::find()->orderBy('id DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' =>['']]
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
            'prize_num' => $this->prize_num,
            'status' => $this->status,
            'type' => $this->type,
            'starttime' => $this->starttime,
            'endtime' => $this->endtime,
            'addtime' => $this->addtime,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'prize', $this->prize])
            ->andFilterWhere(['like', 'performer', $this->performer]);

        return $dataProvider;
    }
}
