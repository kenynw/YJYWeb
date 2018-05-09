<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Ask;
use common\functions\Tools;

/**
 * AskSearch represents the model behind the search form about `common\models\Ask`.
 */
class AskSearch extends Ask
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['askid', 'user_id', 'status', 'product_id','type','userType'], 'integer'],
            [['subject', 'content', 'username', 'product_name'], 'safe'],
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
        //$query = Ask::find();

        $query = Ask::find()
            ->select("t.askid id,t.subject,t.content,t.a_name,t.product_name,t.reply,t.r_name,t.type,t.total,t.add_time,t.a_uid,t.r_uid,t.replyid,t.product_id,t.picture")
            ->leftJoin('(
(SELECT a.askid,a.subject,a.content,a.username a_name,a.user_id a_uid,a.admin_id a_aid,a.product_id,a.product_name,r.reply,r.username r_name,r.user_id r_uid,r.admin_id r_aid,a.add_time,"1" type,count(r.askid) total,replyid,r.picture picture FROM `yjy_ask` a
LEFT JOIN `yjy_ask_reply` r on a.askid = r.askid
group by a.askid)
UNION ALL
(SELECT a.askid,a.subject,a.content,a.username a_name,a.user_id a_uid,a.admin_id a_aid,a.product_id,a.product_name,r.reply,r.username r_name,r.user_id r_uid,r.admin_id r_aid,r.add_time,"2" type,"" total,replyid,r.picture picture FROM `yjy_ask_reply` r
LEFT JOIN `yjy_ask` a on a.askid = r.askid)
) AS t','t.askid   = {{%ask}}.askid  ');


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => ['defaultOrder' => ['add_time' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'askid' => $this->askid,
//             'user_id' => $this->user_id,
            'status' => $this->status,
            't.product_id' => $this->product_id,
            'add_time' => $this->add_time,
            't.type' => $this->type,
        ]);

        $query->andFilterWhere(['like', 't.subject', Tools::userTextEncode($this->subject)])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 't.product_name', $this->product_name]);

        if (!empty($params['AskSearch']['user_id'])) {
            $cond = ['or', ['and' , 'type=1', ['=', 'a_uid',$params['AskSearch']['user_id']]], ['and' , 'type=2',['=', 'r_uid',$params['AskSearch']['user_id']] ]];
            $query->andWhere($cond);
        }
        
        //马甲和真实用户搜索
        if ($this->userType == 1) {
//             $list = User::find()->where("admin_id=0")->asArray()->limit("300")->column();
//             $cond = ['or', ['and' , 'type=1',['in', 'a_uid',$list]], ['and' , 'type=2',['in', 'r_uid',$list] ]];
            $query->andWhere(['or', ['and' , 'type=1','a_aid=0'], ['and' , 'type=2','r_aid=0' ]]);
        }else if ($this->userType == 2) {
//             $list = User::find()->where("admin_id!=0")->asArray()->limit("300")->column();
//             $cond = ['or', ['and' , 'type=1',['in', 'a_uid',$list]], ['and' , 'type=2',['in', 'r_uid',$list] ]];
            $query->andWhere(['or', ['and' , 'type=1','a_aid<>0'], ['and' , 'type=2','r_aid<>0' ]]);
        }
//         print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
