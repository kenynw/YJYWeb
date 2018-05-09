<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Brand;
use yii\sphinx\Query;
use common\functions\Functions;
use yii\sphinx\MatchExpression;

/**
 * BrandSearch represents the model behind the search form about `common\models\Brand`.
 */
class BrandSearch extends Brand
{
    public $product_num;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'cate_id', 'is_recommend','status','is_link','parent_id'], 'integer'],
            [['name', 'ename', 'img', 'description'], 'safe'],
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
        $query = Brand::find()
            ->select("{{%brand}}.*,pd.product_num,CASE WHEN {{%brand}}.`link_tb` <> '' OR {{%brand}}.`link_jd` <> '' THEN 0 ELSE 1 END AS is_link")
            ->leftJoin("(SELECT count(id) as product_num,brand_id FROM {{%product_details}} GROUP BY brand_id) as pd","pd.brand_id = {{%brand}}.id");
//             ->joinWith('productDetails pd')
//             ->groupBy('pd.brand_id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => ['attributes' => ['created_at','hot','product_num'],'defaultOrder' => ['created_at' => SORT_DESC]],            
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
            'cate_id' => $this->cate_id,
            'status' => $this->status,
            'hot' => $this->hot,
            'is_recommend' => $this->is_recommend,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
        ]);

        //品牌名搜索
        if (!empty($params['BrandSearch']['name']) || !empty($params['BrandSearch']['ename'])) {
            $sphinx_query = new Query();
            $rows = $sphinx_query->select('id')->from('brand')->match((new MatchExpression())
                ->match($this->name)
                ->andMatch($this->ename)
            )->limit(1000)->all();
            if (!empty($rows)) {
                foreach ($rows as $key => $value) {
                    $idArr[] = $value['id'];
                }
                $idStr = Functions::db_create_in($idArr,'{{%brand}}.id');
                $query->andWhere("$idStr");
            } else {
                $query->andFilterWhere(['or',['like', 'name', $this->name],['like', 'ename', $this->ename]]);
            }
        }
        
        $query->andFilterWhere(['like', 'img', $this->img])
        ->andFilterWhere(['like', 'description', $this->description]);
        
        //有无返利
        if (!empty($params['BrandSearch']['is_link'])) {
            if ($params['BrandSearch']['is_link'] == '2') {
                $query->andWhere("link_tb <> '' OR link_jd <> ''");
            } else {
                $query->andWhere("link_tb = '' AND link_jd = ''");
            }
        }
        
        //起始时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%brand}}.created_at', $startTime, $endTime]);
        }

        return $dataProvider;
    }
}
