<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\UserFileEvents;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UserFileEventsSearch extends UserFileEvents
{
    public $tab;
    public $file_created_t;
    //public $created_at_range;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'event_id',
                'event_type',
                'file_id',
                'node_id',
                'user_id',
                'event_creator_user_id',
                'event_creator_node_id',
                'parent_before_event',
                'parent_after_event'], 'integer'],
            [[
                'file_name_before_event',
                'file_name_after_event',
                'event_timestamp',
                //'created_at_range',
                'tab'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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
     * @param integer $user_id
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($user_id, $params)
    {
        $query = self::find()
            ->with('node')
            //->select("*, extract(epoch from file_created) as file_created_t")
            ->where(['user_id' => $user_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => array_merge($_GET, ['tab' => 'event-info', '#' => 'event-info']),
                'sortParam' => 'sort-p3',
                'defaultOrder' => ['event_id'=>SORT_DESC],
                'attributes' => [
                    'event_id',
                    'file_id',
                    'event_timestamp',
                    'event_type',
                ]
            ],
            'pagination' => [
                'params' => array_merge($_GET, ['tab' => 'event-info', '#' => 'event-info']),
                'pageParam' => 'p-ev-inf',
                'pageSizeParam' => 'per-p-ev-inf',
                'pageSize' => 100,
                //'route' => \yii\helpers\Url::current(['tab' => 'node-info', 'id' => $user_id])
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'event_id'            => $this->event_id,
            'file_id'             => $this->file_id,
            'parent_before_event' => $this->parent_before_event,
            'parent_after_event'  => $this->parent_after_event,
            'event_type'          => $this->event_type,
        ]);

        $query->andFilterWhere(['ilike', 'file_name_after_event',  $this->file_name_after_event])
              ->andFilterWhere(['ilike', 'file_name_before_event', $this->file_name_before_event]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->event_timestamp) && strpos($this->event_timestamp, '-') !== false) {
            $tmp = explode(' - ', $this->event_timestamp);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'event_timestamp',
                    Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date)),
                    Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date)),
                ]);
            }
        }

        //$query->andFilterWhere(['like', 'file_name', $this->file_name]);

        return $dataProvider;
    }
}
