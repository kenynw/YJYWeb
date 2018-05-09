<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductLink;

/**
 * ProductLinkSearch represents the model behind the search form about `common\models\ProductLink`.
 */
class ProductLinkSearch extends ProductLink
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id','status','type','product_id'], 'integer'],
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
        $query = ProductLink::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => ['add_time'],
                'defaultOrder' => ['add_time' => SORT_DESC,]
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
            'id' => $this->id,
            'product_id' => $this->product_id,
            'tb_goods_id' => $this->tb_goods_id,
            'type' => $this->type,
            'link_price' => $this->link_price,
            'update_time' => $this->update_time,
            'add_time' => $this->add_time,
            'admin_id' => $this->admin_id,
//             'status' => $this->status,
        ]);
        
        //时间搜索
        if (!empty($params['add_time'])) {
            $startTime = strtotime($params['add_time']);
            $endTime = $startTime + 86399;
            $query->andFilterWhere(['between', 'add_time', $startTime, $endTime]);
        } else {
//             $startTime = strtotime(date('Y-m-d',strtotime('-1 day')));
//             $endTime = $startTime + 86399;
//             $query->andFilterWhere(['between', 'add_time', $startTime, $endTime]);
        }

        //状态搜索
        if (!empty($params['ProductLinkSearch']['status'])) {
            $status = $params['ProductLinkSearch']['status'];
            switch($status)
            {
                case "1":
                    $query->andWhere("url <> '' AND status = 1");
                    break;
                case "2":
                    $query->andWhere("url = '' AND status = 0");
                    break;
                case "3":
                    $query->andWhere("url = '' AND status = 1");
                    break;
            }
        }

        $query->andFilterWhere(['like', 'url', $this->url]);                

        return $dataProvider;
    }
}
