<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\db\Expression;
use common\functions\Tools;
use common\functions\Functions;


class UserSearch extends User
{
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at','admin_id'], 'integer'],
            [['username', 'mobile','referer','userType'], 'safe'],
            [['id', 'mobile','username'], 'trim'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = User::find()
            ->select("{{%user}}.*,user_product_num,user_inventory_num,count(t.product_num) product_comment_num,count(t.article_num) acticle_comment_num,feedback_num,askreply_num")
            ->leftJoin('( (SELECT user_id,id product_num,null article_num FROM `yjy_comment` where type=1 and status = 1 and parent_id = 0) union (SELECT user_id,null product_num,id article_num FROM `yjy_comment` where type=2 and status = 1 and parent_id = 0) ) as t','t.user_id = {{%user}}.id')
            ->leftJoin('(SELECT user_id,count(id) feedback_num FROM yjy_user_feedback group by user_id) AS f','f.user_id = {{%user}}.id')
            ->leftJoin('(SELECT user_id,count(ask_num)+count(reply_num) askreply_num FROM (SELECT * FROM ((SELECT user_id,askid ask_num,null reply_num FROM yjy_ask) union (SELECT user_id,null ask_num,replyid reply_num FROM yjy_ask_reply) ) AS a1) AS a2 GROUP BY user_id) AS a3','a3.user_id = {{%user}}.id')
            ->leftJoin('(SELECT user_id,count(id) user_product_num FROM yjy_user_product group by user_id) AS up','up.user_id = {{%user}}.id')
            ->leftJoin('(SELECT user_id,count(id) user_inventory_num FROM yjy_user_inventory group by user_id) AS ui','ui.user_id = {{%user}}.id')
            ->groupBy('{{%user}}.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
        ]);

        $dataProvider->setSort([
            'attributes' =>['id' ,'created_at','user_product_num'=>['default' => SORT_DESC],'user_inventory_num'=>['default' => SORT_DESC],'product_comment_num'=>['default' => SORT_DESC],'acticle_comment_num'=>['default' => SORT_DESC],'feedback_num'=>['default' => SORT_DESC],'askreply_num'=>['default' => SORT_DESC]],
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            '{{%user}}.id' => $this->id,
            'admin_id' => $this->admin_id,
            'referer' => $this->referer,
            'status' => $this->status
        ]);

        if (!empty($params['UserSearch']['mobile'])) {
            $query->andFilterWhere(['regexp', 'mobile', '^[0-9]+$'])->andFilterWhere(['like', 'mobile', $this->mobile]);
        }
        
        $query->andFilterWhere(['like', 'username', Tools::userTextEncode($this->username)]);
        
        //注册时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%user}}.created_at', $startTime, $endTime]);
        }

        //马甲和真实用户搜索
        if ($this->userType == 1) {
            $query->andWhere("{{%user}}.admin_id=0");
        }else if ($this->userType == 2) {
            $query->andFilterWhere(['<>', '{{%user}}.admin_id', '0']);
        }
//                 print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
