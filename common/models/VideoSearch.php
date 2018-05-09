<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Video;

/**
 * VideoSearch represents the model behind the search form about `common\models\Video`.
 */
class VideoSearch extends Video
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status','is_complete','id'], 'integer'],
            [['title','add_time','type','update_time'], 'safe'],
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
        $query = Video::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '10',
            ],
            'sort' => [
                'attributes' => ['update_time'],
                'defaultOrder' => ['update_time' => SORT_DESC,]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        //时间搜索
        if ((!empty($params['start_at']) && !empty($params['end_at'])) || (empty($params['start_at']) && !empty($params['end_at']))) {
            $startTime = empty($params['start_at']) ? 0 : strtotime($params['start_at']);
            $endTime = strtotime($params['end_at']) + 86399;
            $query->andFilterWhere(['between', '{{%video}}.update_time', $startTime, $endTime]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'click_num' => $this->click_num,
            'like_num' => $this->like_num,
            'comment_num' => $this->comment_num,
            'filesize' => $this->filesize,
            'duration' => $this->duration,
            'is_complete' => 1,
            'status' => $this->status,
            'type' => $this->type,
            'add_time' => $this->add_time,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'video', $this->video])
            ->andFilterWhere(['like', 'thumb_img', $this->thumb_img])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'product_id', $this->product_id])
            ->andFilterWhere(['like', 'link_url', $this->link_url])
            ->andFilterWhere(['like', 'ext', $this->ext]);

//                        print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
