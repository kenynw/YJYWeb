<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductCategory;

/**
 * ProductCategorySearch represents the model behind the search form about `common\models\ProductCategory`.
 */
class ProductCategorySearch extends ProductCategory
{
    /**
     * @inheritdoc
     */
    public $product_num;
    public function rules()
    {
        return [
            [[/*'id',*/'parent_id','status', 'created_at'], 'integer'],
            [[/*'cate_name', 'cate_img'*/], 'safe'],
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
        $query = ProductCategory::find()
            ->select("id,cate_name,cate_h5_img,cate_app_img,sort,created_at,CASE WHEN `parent_id` = '0' THEN id ELSE `parent_id` END AS parent_id,CASE WHEN `parent_id` = '0' THEN 100 ELSE 1 END AS p_id,`status`,t.product_num ")
            ->leftJoin('(SELECT count(id) product_num,cate_id FROM `yjy_product_details` GROUP BY cate_id) as t','t.cate_id = {{%product_category}}.id')
            ->orderBy('parent_id DESC,p_id DESC,sort');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '40',
            ],
            'sort' => [
                'attributes' =>['created_at','product_num','sort','status'],
                'defaultOrder' => [
//                     'status' => SORT_DESC,
//                      'sort' => SORT_DESC,
//                     'created_at' => SORT_DESC,
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
            '{{%product_category}}.status' => $this->status,
//             '{{%product_category}}.parent_id' => $this->parent_id,
//             'created_at' => $this->created_at,
        ]);

//         $query->andFilterWhere(['like', 'cate_name', $this->cate_name])
//             ->andFilterWhere(['like', 'cate_img', $this->cate_img]);

        //所属类别搜索
        if (!empty($params['ProductCategorySearch']['parent_id'])) {
            $query->andFilterWhere(['{{%product_category}}.parent_id' => $this->parent_id]);
            $query->orFilterWhere(['{{%product_category}}.id' => $this->parent_id]);
        }
        
        //起始时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%product_category}}.created_at', $startTime, $endTime]);
        }
//                 print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
