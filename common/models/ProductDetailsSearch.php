<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductDetails;
use yii\base\Object;
use yii\sphinx\Query;
use common\functions\Functions;
use yii\sphinx\MatchExpression;

/**
 * ProductDetailsSearch represents the model behind the search form about `common\models\ProductDetails`.
 */
class ProductDetailsSearch extends ProductDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'is_recommend', 'cate_id', 'star', 'product_date', 'created_at','has_img','is_top'], 'integer'],
            [['product_name', 'brand_id', 'standard_number', 'product_country', 'product_company', 'en_product_company', 'component_id','is_link'], 'safe'],
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
        $query = ProductDetails::find()
            ->select("{{%product_details}}.*,askreply_num")
            ->leftJoin('(SELECT product_id,SUM(askreply_num) askreply_num FROM (SELECT a.product_id,p.askid,CASE WHEN p.askid IS null THEN 1 ELSE 2 END AS askreply_num FROM yjy_ask a LEFT JOIN yjy_ask_reply p ON a.askid = p.askid) x GROUP BY product_id) AS ar','ar.product_id = {{%product_details}}.id');
        
        //成分产品
        if (isset($params['ProductDetailsSearch']['component_id'])) {
            $query->joinWith('productRelate pr')->andWhere("pr.component_id = {$params['ProductDetailsSearch']['component_id']}")->groupBy("{{%product_details}}.id");
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
                //'route' => 'product-details/index'
            ],
            'sort' => ['attributes' => ['created_at','comment_num'=>['default' => SORT_DESC],'askreply_num'=>['default' => SORT_DESC]]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '{{%product_details}}.id' => $this->id,
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
        if (!empty($params['ProductDetailsSearch']['product_name']) && !empty(trim($params['ProductDetailsSearch']['product_name']))) {
            //批量抓取产品跳转
            if (isset($params['grap'])) {
                $idArr = unserialize($params['grap']);
                $idStr = !empty($idArr) ?  Functions::db_create_in($idArr,'id') : '' ;
                $query->andWhere("$idStr");
            }
            
            $idStr      =   '';
            $sphinx_query = new Query();
            $brandId    =   '';
            $idArr      =   [];            
            $brandArr       =   (object)[];
            $searchBrandId  = '';
            $brandIdArr     = '';
            $orderBrandByArr = [];
                        
            $search     =   Functions::checkStr($params['ProductDetailsSearch']['product_name']);
            
            if ($search) {
//              //先匹配品牌
                $brandSql   = "SELECT id,name,img,hot FROM {{%brand}} WHERE status = '1' AND (name = '$search' || ename = '$search' || alias = '$search')";
                $brandArr   = Yii::$app->db->createCommand($brandSql)->queryOne();//var_dump($brandArr);die;
                if($brandArr){
                    $searchBrandId    = $brandArr['id'];
                }
                //查询产品
                $rows       =   $sphinx_query->select('id')->from('product')->match($search)->limit(1000)->all();
                //如果有子品牌还加子品牌
                if($searchBrandId){
                    $cate_arr           = Functions::getBrandColumn($searchBrandId);
                    if($cate_arr){
                        $brandIdArr     = Functions::getProductCateArr($cate_arr);
                    }
                    $productBrandSql= Functions::db_create_in($searchBrandId.','. $brandIdArr,'brand_id');
                    $pidSql         = "SELECT id FROM {{%product_details}} WHERE $productBrandSql";
                    $pRows          = Yii::$app->db->createCommand($pidSql)->queryAll();
                    foreach ($pRows as $key => $value) {
                        $idArr[] = $value['id'];
                    }
                    
                    if (empty($brandIdArr)) {
                        $brandIdArr = $searchBrandId;
                    } else {
                        $brandIdArr = $searchBrandId.','.$brandIdArr;
                    }
                }
                foreach ($rows as $key => $value) {
                    $idArr[] = $value['id'];
                }
                
                $idArr = array_unique($idArr);
//                 if (!empty($idArr)) {
                    $idStr = Functions::db_create_in($idArr,'id');
                    $query->andWhere("$idStr");
//                 } else {
//                     $query->andFilterWhere(['like', 'product_name', $this->product_name]);
//                 }         
                
                if (!empty($brandIdArr)) {
                    $orderBy = "is_top DESC,comment_num DESC,is_recommend DESC,is_complete DESC,has_img DESC,has_price DESC,has_brand DESC,created_at DESC,id DESC";
                    $query->orderBy($orderBy);

                    $orderBrandByArr["brand_id in ($brandIdArr)"] = 3;
                    $query->orderBy = $orderBrandByArr + $query->orderBy;
                } else {
                    $orderBy = "comment_num DESC,is_recommend DESC,is_complete DESC,has_img DESC,has_price DESC,has_brand DESC,created_at DESC,id DESC";
                    $query->orderBy($orderBy);
                }
            }     
        } else {
            if (!isset($params['sort'])) {
                $query->orderBy('`has_img` AND `price` DESC ,is_recommend DESC,recommend_time DESC,is_top DESC,has_img DESC,comment_num DESC,star DESC,{{%product_details}}.id DESC');
            }
        }
        
        //批量抓取产品跳转
        if (empty($params['ProductDetailsSearch']['product_name']) && isset($params['grap'])) {
            $idArr = unserialize($params['grap']);
            $idStr = !empty($idArr) ?  Functions::db_create_in($idArr,'id') : '' ;
            $query->andWhere("$idStr");
        }
        
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
        
        //有无返利
        if (!empty($params['ProductDetailsSearch']['is_link'])) {
            $idArr = ProductLink::find()->select('product_id')->groupBy('product_id')->column();
            if ($params['ProductDetailsSearch']['is_link'] == '1') {
                $idStr = join(',', $idArr);
                $query->andWhere("id NOT IN ($idStr)");
            } else {
                $idStr = Functions::db_create_in($idArr,'id');
                $query->andWhere("$idStr");
            }
        }

        //文章添加/编辑页
        if (!empty($params['del_ids'])) {
            $cond = ['in', 'id', $params['del_ids']];
            $query->andFilterWhere($cond);
        }
        if (!empty($params['ids'])) {
            $params['ids'] = explode(",",$params['ids']);
            $cond = ['not in', 'id', $params['ids']];
            $query->andFilterWhere($cond);
        }

        
//                print_r($query->createCommand()->getRawSql());die;

        return $dataProvider;
    }


}
