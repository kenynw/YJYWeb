<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AdminLog;

/**
 * AdminLogSearch represents the model behind the search form about `common\models\AdminLog`.
 */
class AdminLogSearch extends AdminLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at','type'], 'integer'],
            [['route', 'description'], 'safe'],
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
        $query = AdminLog::find();

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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'route', $this->route])
            ->andFilterWhere(['like', 'description', $this->description]);
        
        //时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%admin_log}}.created_at', $startTime, $endTime]);
        }

        return $dataProvider;
    }
    
    public function viewSearch($params)
    {
        $query = AdminLog::find()->alias('a');
    
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
        
        //详情展示
        if (!empty($params['type']) && !empty($params['id'])) {
            if ($params['type'] == 'brand') {
                $query->rightJoin("((SELECT * FROM
                (SELECT *,FROM_UNIXTIME(created_at,'%Y-%m-%d') AS date,CASE WHEN description REGEXP '是否上榜' THEN 1 ELSE 2 END AS type FROM `yjy_admin_log` WHERE `route` LIKE '%brand%' AND `route` NOT LIKE '%/brand/change-status%' AND `description` LIKE '%品牌 : 0 => {$params['id']}%' ORDER BY created_at DESC) c
                GROUP BY type,FROM_UNIXTIME(created_at,'%Y-%m-%d'),user_id) 
                UNION (SELECT *,NULL date,NULL type FROM `yjy_admin_log` WHERE `route` LIKE '%brand%' AND `route` NOT LIKE '%/brand/change-status%' AND `description` REGEXP '《yjy_brand》.*id为{$params['id']}') ORDER BY created_at DESC) AS b","a.id = b.id");
            } elseif ($params['type'] == 'product-details') {
                $query = $query->select("a.*,type");
                $query->rightJoin("((SELECT *,NULL date,1 type FROM yjy_admin_log 
                    WHERE route LIKE '%product-details%'
                    AND 
                    description REGEXP 'id为{$params['id']}.*产品图 :.* product_img')
                    UNION
                    (
                    SELECT *,NULL date,2 type FROM yjy_admin_log 
                    WHERE (route LIKE '%product-details%'
                    AND 
                    description REGEXP '表《yjy_product_link》.*Product ID : {$params['id']}') OR (route LIKE '/product-details/update?id={$params['id']}%'
                    AND description REGEXP '表《yjy_product_link》'))
                    UNION
                    (SELECT * FROM
                    (
                    SELECT *,FROM_UNIXTIME(created_at,'%Y-%m-%d') AS date,CASE WHEN description REGEXP '新增：表《yjy_comment》' OR description REGEXP '新增.*父级id : 0' THEN 3 ELSE 4 END AS type FROM yjy_admin_log  
                    WHERE route REGEXP '/comment.*post_id={$params['id']}.*product-details'
                    AND 
                    description REGEXP '表《yjy_comment》'
                    OR
					(route REGEXP '/comment/update.*post_id={$params['id']}.*product-details' AND description REGEXP '表《yjy_attachment》')
                    ORDER BY created_at DESC
                    ) AS c
                    GROUP BY type,date,user_id)) AS b ",'a.id = b.id');
            }
        }
        if (!empty($params['AdminLogSearch']['type'])) {
            if ($this->type == '3') {
                $query->andWhere("type = 3 OR type = 4");
            } else {
                $query->andFilterWhere([
                    'type' => $this->type,
                ]);
            }
        }
//                         print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
