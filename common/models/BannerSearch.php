<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Banner;
use yii\behaviors\TimestampBehavior;

class BannerSearch extends Banner
{
    public $status;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'sort_id','position'], 'integer'],
            [['title', 'url','status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Banner::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' =>['sort_id','start_time','end_time','status'],
                'defaultOrder' => [
                    'status' => SORT_DESC,
                    'start_time' => SORT_DESC,
                ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'sort_id' => $this->sort_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'position' => $this->position,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
