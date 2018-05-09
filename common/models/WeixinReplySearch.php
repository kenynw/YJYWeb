<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\WeixinReply;

/**
 * WeixinReplySearch represents the model behind the search form about `common\models\WeixinReply`.
 */
class WeixinReplySearch extends WeixinReply
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['id'], 'integer'],
            [['keyword', 'reply','type','match_mode'], 'safe'],
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
        $query = WeixinReply::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => ['attributes' => ['add_time'],'defaultOrder' => ['add_time' => SORT_DESC]],
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
            'add_time' => $this->add_time,
            'type' => $this->type,
            'match_mode' => $this->match_mode
        ]);

        $query->andFilterWhere(['like', 'keyword', $this->keyword])
            ->andFilterWhere(['like', 'reply', $this->reply]);

        return $dataProvider;
    }
}
