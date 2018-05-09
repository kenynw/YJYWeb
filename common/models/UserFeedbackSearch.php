<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserFeedback;
use common\functions\Tools;

/**
 * UserFeedbackSearch represents the model behind the search form about `common\models\UserFeedback`.
 */
class UserFeedbackSearch extends UserFeedback
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'source', 'telphone'], 'integer'],
            [['content'], 'safe'],
            [['username','number'],'string']
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
        $query = UserFeedback::find()
//                  ->alias('f1')
//                  ->select("f1.id,f1.user_id,f1.username,f1.source,f1.number,f1.model,f1.system,f1.content,f1.created_at")
//                  ->rightJoin("(SELECT * from yjy_user_feedback ORDER BY created_at DESC) AS f2","f1.id = f2.id")
//                  ->groupBy("f1.user_id")
//                  ->orderBy("f1.created_at DESC");
                ->select("f4.*")
                ->alias('f1')
                ->rightJoin("(SELECT f2.* FROM `yjy_user_feedback` `f2` RIGHT JOIN (SELECT * from yjy_user_feedback ORDER BY created_at DESC) AS f3 ON f2.id = f3.id GROUP BY `f2`.`user_id` ORDER BY `f2`.`created_at` DESC) AS f4","f1.id = f4.id");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' =>['created_at','user_id'],
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
            'f1.id' => $this->id,
            'f1.user_id' => $this->user_id,
            'f1.source' => $this->source,
            'f1.created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'f1.content', $this->content])
              ->andFilterWhere(['like', 'f1.username', Tools::userTextEncode($this->username)])
              ->andFilterWhere(['like', 'f1.number', $this->number])
              ->andFilterWhere(['like', 'f1.telphone', $this->telphone]);

        return $dataProvider;
    }
}
