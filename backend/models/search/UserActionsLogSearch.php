<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\UserActionsLog;

/**
 * UserActionsLogSearch represents the model behind the search form of `common\models\UserActionsLog`.
 */
class UserActionsLogSearch extends UserActionsLog
{
    public $_user_email;
    public $tab;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['record_id', 'user_id'], 'integer'],
            [['tab', '_user_email', 'action_created', 'action_url', 'action_type', 'action_raw_data', 'site_url', 'site_absolute_url'], 'safe'],
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
     * @param integer $user_id
     *
     * @return ActiveDataProvider
     */
    public function search($params, $user_id=null)
    {
        //$query = UserActionsLog::find();
        $query = self::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email as _user_email")
            ->leftJoin('{{%users}} as t2', 't2.user_id = t1.user_id');

        if ($user_id) {
            $query->where(['t1.user_id' => $user_id]);
        }

        // add conditions that should always apply here

        if ($user_id) {
            $params = array_merge($_GET, ['tab' => 'action-logs', '#' => 'action-logs']);
        } else {
            $params = $_GET;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => $params,
                'sortParam' => 'sort-act',
                'defaultOrder' => ['action_created'=>SORT_DESC],
                'attributes' => [
                    'action_created',
                    'action_type',
                    'action_url',
                    'record_id',
                    'user_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                ]
            ],
            'pagination' => [
                'params' => $params,
                'pageParam' => 'p-act-inf',
                'pageSizeParam' => 'per-p-act-inf',
                'pageSize' => 100,
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
            'record_id' => $this->record_id,
            //'action_created' => $this->action_created,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['ilike', 'action_url', $this->action_url])
            ->andFilterWhere(['ilike', 'action_type', $this->action_type])
            ->andFilterWhere(['ilike', 'action_raw_data', $this->action_raw_data])
            ->andFilterWhere(['ilike', 'site_url', $this->site_url])
            ->andFilterWhere(['ilike', 'site_absolute_url', $this->site_absolute_url]);

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->action_created) && strpos($this->action_created, '-') !== false) {
            $tmp = explode(' - ', $this->action_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'action_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }
}
