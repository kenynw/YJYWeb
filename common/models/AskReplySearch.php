<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AskReply;

/**
 * AskReplySearch represents the model behind the search form about `common\models\AskReply`.
 */
class AskReplySearch extends AskReply
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['replyid', 'askid', 'user_id', 'add_time'], 'integer'],
            [['reply', 'username'], 'safe'],
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
        $query = AskReply::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'replyid' => $this->replyid,
            'askid' => $this->askid,
            'user_id' => $this->user_id,
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'reply', $this->reply])
            ->andFilterWhere(['like', 'username', $this->username]);

        return $dataProvider;
    }
}
