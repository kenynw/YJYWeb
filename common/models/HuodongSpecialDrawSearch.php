<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\HuodongSpecialDraw;

/**
 * HuodongSpecialDrawSearch represents the model behind the search form about `common\models\HuodongSpecialDraw`.
 */
class HuodongSpecialDrawSearch extends HuodongSpecialDraw
{
    /**
     * @inheritdoc
     */
    public $information;
    public function rules()
    {
        return [
            [['uid', 'giftid', 'sendstatus','giftid'], 'integer'],
            [['username', 'giftname', 'ip', 'endtime' , 'hdid'], 'safe'],
            [['information'], 'string'],
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
        $query = HuodongSpecialDraw::find()
            ->joinWith('huodongAddress')
            ->where("giftid != 0")
            ->groupBy('{{%huodong_special_draw}}.id')
            ->orderBy('hdid DESC,{{%huodong_special_draw}}.endtime DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'id' => $this->id,
            'hdid' => $this->hdid,
            'uid' => $this->uid,
            'prize' => $this->prize,
            'giftid' => $this->giftid,
            'addtime' => $this->addtime,
            'sendstatus' => $this->sendstatus,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'giftname', $this->giftname])
            ->andFilterWhere(['like', 'ip', $this->ip]);
//             ->andFilterWhere(['like', '{{%huodong_special_config}}.name', $this->hdid]);
        
        //时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%huodong_special_draw}}.endtime', $startTime, $endTime]);
        }
        
        //收货信息搜索
        if (!empty($params['HuodongSpecialDrawSearch']['information'])) {
            $query->andFilterWhere(['or',['like', '{{%huodong_address}}.name', $this->information],['like', '{{%huodong_address}}.tel', $this->information],['like', '{{%huodong_address}}.address', $this->information]]);
        }
//                print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
