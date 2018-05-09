<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserInventoryProduct;

/**
 * UserInventoryProductSearch represents the model behind the search form about `common\models\UserInventoryProduct`.
 */
class UserInventoryProductSearch extends UserInventoryProduct
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//             [['id', 'product_id', 'invent_id', 'order'], 'integer'],
//             [['desc', 'add_time'], 'safe'],
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
        $query = UserInventoryProduct::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' =>['add_time'],'defaultOrder' => ['add_time' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        if (!empty($params['UserInventorySearch']['invent_id'])) {
            $query->where("invent_id = {$params['UserInventorySearch']['invent_id']}");
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'product_id' => $this->product_id,
            'invent_id' => $this->invent_id,
            'order' => $this->order,
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'desc', $this->desc]);
        
//                        print_r($query->createCommand()->getRawSql());die;

        return $dataProvider;
    }
}
