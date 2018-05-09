<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SkinBaike;

/**
 * SkinBaikeSearch represents the model behind the search form about `common\models\SkinBaike`.
 */
class SkinBaikeSearch extends SkinBaike
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['skin_id', 'order', 'add_time'], 'integer'],
            [['skin_name', 'question'], 'safe'],
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
        $query = SkinBaike::find()->orderBy('id DESC');

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
            'skin_id' => $this->skin_id,
            'order' => $this->order,
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'skin_name', $this->skin_name])
            ->andFilterWhere(['like', 'question', $this->question]);

        return $dataProvider;
    }
}
