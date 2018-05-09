<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserProduct;

/**
 * UserProductSearch represents the model behind the search form about `common\models\UserProduct`.
 */
class UserProductSearch extends UserProduct
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_seal','user_id'], 'integer'],
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
        $query = UserProduct::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => ['attributes' =>['add_time'],'defaultOrder' => ['add_time' => SORT_DESC]],        
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
            'user_id' => $this->user_id,
            'brand_id' => $this->brand_id,
//             'is_seal' => $this->is_seal,
            'seal_time' => $this->seal_time,
            'quality_time' => $this->quality_time,
            'overdue_time' => $this->overdue_time,
            'days' => $this->days,
            'expire_time' => $this->expire_time,
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'brand_name', $this->brand_name])
            ->andFilterWhere(['like', 'product', $this->product])
            ->andFilterWhere(['like', 'img', $this->img]);
        
        if (!empty($params['UserProductSearch']['is_seal'])) {
            if ($params['UserProductSearch']['is_seal'] == '1') {
                $query->andWhere("is_seal = 0");
            } else {
                $time = strtotime(date('Y-m-d',time()));
                
                if ($params['UserProductSearch']['is_seal'] == '2') {
                    $query->andWhere("is_seal = 1 AND overdue_time >= $time AND CASE WHEN quality_time = '' THEN UNIX_TIMESTAMP(date_add(FROM_UNIXTIME(seal_time), interval days DAY)) ELSE UNIX_TIMESTAMP(date_add(FROM_UNIXTIME(seal_time), interval quality_time MONTH)) END >= $time");
                } 
                if ($params['UserProductSearch']['is_seal'] == '3') {
                    $query->andWhere("is_seal = 1 AND (overdue_time < $time OR CASE WHEN quality_time = '' THEN UNIX_TIMESTAMP(date_add(FROM_UNIXTIME(seal_time), interval days DAY)) ELSE UNIX_TIMESTAMP(date_add(FROM_UNIXTIME(seal_time), interval quality_time MONTH)) END < $time)");
                }
            }
        }
//         print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
