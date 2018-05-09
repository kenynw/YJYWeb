<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductDetails;
use yii\base\Object;
use common\functions\Functions;
use yii\sphinx\Query;
use yii\sphinx\MatchExpression;

/**
 * ProductDetailsSearch represents the model behind the search form about `common\models\ProductDetails`.
 */
class BrandProductDetailsSearch extends ProductDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'is_recommend', 'cate_id', 'star', 'product_date', 'created_at','has_img','is_top'], 'integer'],
            [['product_name', 'brand_id', 'standard_number', 'product_country', 'product_company', 'en_product_company', 'component_id'], 'safe'],
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
        $query = ProductDetails::find()->where("brand_id = 0")->orderBy('is_recommend DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'created_at'
            ],
            'defaultOrder' => [
                'created_at' => SORT_DESC,
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
            'status' => $this->status,
            'has_img' => $this->has_img,
            'is_recommend' => $this->is_recommend,
            'price' => $this->price,
            'star' => $this->star,
            'is_top' => $this->is_top,
            'brand_id' => $this->brand_id
//             'cate_id' => $this->cate_id,
//             'product_date' => $this->product_date,
        ]);

        $query->andFilterWhere(['like', 'form', $this->form]);
//             ->andFilterWhere(['like', 'standard_number', $this->standard_number])
//             ->andFilterWhere(['like', 'product_country', $this->product_country])
//             ->andFilterWhere(['like', 'product_company', $this->product_company])
//             ->andFilterWhere(['like', 'en_product_company', $this->en_product_company])
//             ->andFilterWhere(['like', 'component_id', $this->component_id]);
        
        //产品名搜索
        if (!empty($params['BrandProductDetailsSearch']['product_name'])) {
            $search     =   Functions::checkStr($params['BrandProductDetailsSearch']['product_name']);
            $sphinx_query      = new Query();
            $rows       = $sphinx_query->select('id')->from('product')->match($search)->limit(1000)->all();
            if (!empty($rows)) {
                foreach ($rows as $key => $value) {
                    $idArr[] = $value['id'];
                }
                $idStr = Functions::db_create_in($idArr,'id');
                $query->andWhere("$idStr");
            } else {
                $query->andFilterWhere(['like', 'product_name', $this->product_name]);
            }
        }
        
        //起始时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%product_details}}.created_at', $startTime, $endTime]);
        }

        //分类搜索
        if (!empty($params['ProductDetailsSearch']['cate_id']) && $params['ProductDetailsSearch']['cate_id'] == '未设置') {
            $query->andFilterWhere(['{{%product_details}}.cate_id' => '0']);
        } else {
            $query->andFilterWhere(['{{%product_details}}.cate_id' => $this->cate_id]);
        }
//         var_dump($query->createCommand()->getRawSql());


        //文章添加/编辑页
        if (!empty($params['del_ids'])) {
            $cond = ['in', 'id', $params['del_ids']];
            $query->andFilterWhere($cond);
        }


        return $dataProvider;
    }


}
