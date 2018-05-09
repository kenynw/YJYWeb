<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductBonus;

/**
 * ProductBonusSearch represents the model behind the search form about `common\models\ProductBonus`.
 */
class ProductBonusSearch extends ProductBonus
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id','status','type','product_id','data_type'], 'integer'],
            [['goods_id','product_id'],'trim']
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
        $time = strtotime(date('Y-m-d',time()));
        $query = ProductBonus::find()
            ->joinWith('productDetails p')
            ->where("UNIX_TIMESTAMP(end_date) >= $time")
            ->andWhere("is_off = 1");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['attributes' =>['updated_at','sort','price'=>['default' => SORT_DESC],'status']/* ,'defaultOrder' => ['status' => SORT_DESC,'sort' => SORT_DESC,'updated_at' => SORT_DESC] */]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '{{%product_bonus}}.id' => $this->id,
            '{{%product_bonus}}.goods_id' => $this->goods_id,
            '{{%product_bonus}}.product_id' => $this->product_id,
            'website_type' => $this->website_type,
            '{{%product_bonus}}.price' => $this->price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            '{{%product_bonus}}.status' => $this->status,
            'created_at' => $this->created_at,
            'update_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'goods_link', $this->goods_link])
            ->andFilterWhere(['like', 'bonus_link', $this->bonus_link]);
        
        //有无优惠券
        if (!empty($params['ProductBonusSearch']['type'])) {
            if ($params['ProductBonusSearch']['type'] == '1') {
                $query->andWhere("bonus_link != ''");
            } elseif ($params['ProductBonusSearch']['type'] == '2') {
                $query->andWhere("bonus_link = ''");
            }
        }
        
        //数据类型
        if (!empty($params['ProductBonusSearch']['data_type'])) {
            if ($params['ProductBonusSearch']['data_type'] == '1') {
                $query->andWhere("{{%product_bonus}}.product_id = 0");
            } elseif ($params['ProductBonusSearch']['data_type'] == '2') {
                $query->andWhere("{{%product_bonus}}.product_id <> '0'");
            }
        }
        
        if (!isset($params['sort'])) {
            $query->orderBy('{{%product_bonus}}.status DESC,{{%product_bonus}}.sort DESC,is_manual,price DESC,p.comment_num DESC');
        }

//                        print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
