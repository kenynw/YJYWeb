<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductComponent;

/**
 * ProductComponentSearch represents the model behind the search form about `common\models\ProductComponent`.
 */
class ProductComponentSearch extends ProductComponent
{
    public $product_num;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'risk_grade', 'is_active', 'is_pox', 'created_at'], 'integer'],
            [['name', 'component_action', 'description'], 'safe'],
            [['name'], 'trim'],
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
        $query = ProductComponent::find()
            ->select("{{%product_component}}.*,(SELECT COUNT(distinct product_id) FROM {{%product_relate}} pr WHERE pr.component_id = {{%product_component}}.id) AS `product_num`");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' =>['id','product_num'],
//                 'defaultOrder' => [
//                     'id' => SORT_DESC,
//                 ]
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
            'risk_grade' => $this->risk_grade,
            'is_active' => $this->is_active,
            'is_pox' => $this->is_pox,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'component_action', $this->component_action])
            ->andFilterWhere(['like', 'description', $this->description]);
        
        if (empty($params['ProductComponentSearch']['name'])) {
            $query->orderBy("id DESC");
        } else {
            $query->orderBy("CHAR_LENGTH(name)");
        }
        
        //中英文括号能互搜...
        if (!empty($params['ProductComponentSearch']['name'])) {
            $name = $params['ProductComponentSearch']['name'];
            if (preg_match('/[（）]/',$name)) {
                $str1 = preg_replace ('/（/', '(', $name);
                $str2 = preg_replace ('/）/', ')', $str1);
                $query->orFilterWhere(['like', 'name', $str2]);
            } elseif (preg_match('/[\(\)]/',$name)) {
                $str1 = preg_replace ('/\(/', '（', $name);
                $str2 = preg_replace ('/\)/', '）', $str1);
                $query->orFilterWhere(['like', 'name', $str2]);
            }
        }
//var_dump($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
