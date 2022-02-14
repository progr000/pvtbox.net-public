<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\MessagesStore;

/**
 * MessagesStoreSearch represents the model behind the search form of `common\models\MessagesStore`.
 */
class MessagesStoreSearch extends MessagesStore
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ms_id', 'user_id'], 'integer'],
            [['ms_created', 'ms_type', 'ms_data'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = MessagesStore::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,

            'sort'=> [
                'defaultOrder' => ['ms_created' => SORT_DESC],
                'attributes' => [
                    'ms_type',
                    'ms_created',
                ]
            ],

            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ms_id' => $this->ms_id,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['ilike', 'ms_type', $this->ms_type])
            ->andFilterWhere(['ilike', 'ms_data', $this->ms_data]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->ms_created) && strpos($this->ms_created, '-') !== false) {
            $tmp = explode(' - ', $this->ms_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'ms_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }
}
