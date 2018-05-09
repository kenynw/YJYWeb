<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SkinRecommendProduct;

/**
 * SkinRecommendProductSearch represents the model behind the search form about `common\models\SkinRecommendProduct`.
 */
class SkinRecommendProductSearch extends SkinRecommendProduct
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['skin_id', 'cate_id', 'product_id'], 'integer'],
            [['skin_name'], 'safe'],
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
        $query = SkinRecommendProduct::find()
                ->select('{{%skin_recommend_product}}.*,p.product_name,p.price,p.form,p.star')
                ->joinWith('productDetails p')
                ->joinWith('skin s')
                ->where("{{%skin_recommend_product}}.status = 1")
                ->orderBy('p.`has_img` AND p.`price` DESC ,p.is_recommend DESC,p.recommend_time DESC,p.is_top DESC,p.has_img DESC,p.comment_num DESC,p.star DESC,p.id DESC');               

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pagesize' => '10'],
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
            '{{%skin_recommend_product}}.cate_id' => $this->cate_id,
            '{{%skin_recommend_product}}.product_id' => $this->product_id,
        ]);

        $query->andFilterWhere(['like', 'skin_name', $this->skin_name]);
//         print_r($query->createCommand()->getRawSql());die;

        return $dataProvider;
    }
}
