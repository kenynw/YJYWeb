<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SkinType;

/**
 * SkinTypeSearch represents the model behind the search form about `common\models\SkinType`.
 */
class SkinTypeSearch extends SkinType
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['skin_id', 'min', 'max', 'order', 'add_time'], 'integer'],
//             [['name', 'unscramble'], 'safe'],
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
        $query = SkinType::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [''],
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
            'skin_id' => $this->skin_id,
            'min' => $this->min,
            'max' => $this->max,
            'order' => $this->order,
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'unscramble', $this->unscramble]);

        return $dataProvider;
    }
}
