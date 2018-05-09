<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserAccount;

/**
 * UserAccountSearch represents the model behind the search form about `common\models\UserAccount`.
 */
class UserAccountSearch extends UserAccount
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['id', 'user_id', 'type', 'money', 'created_at', 'updated_at'], 'integer'],
//             [['pay', 'content', 'admin_name', 'remark'], 'safe'],
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
        $query = UserAccount::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => [
                'attributes' =>['created_at'],
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
//             'id' => $this->id,
            'user_id' => $params['UserAccountSearch']['user_id'],
//             'type' => $this->type,
//             'money' => $this->money,
//             'created_at' => $this->created_at,
//             'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'pay', $this->pay])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'admin_name', $this->admin_name])
            ->andFilterWhere(['like', 'remark', $this->remark]);
// var_dump($query->createCommand()->getRawSql());
        return $dataProvider;
    }
}
