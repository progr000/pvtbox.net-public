<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\UserAlertsLog;

/**
 * SearchUserAlertsLog represents the model behind the search form of `common\models\UserAlertsLog`.
 */
class UserAlertsLogSearch extends UserAlertsLog
{
    public $_user_email;
    public $tab;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['record_id', 'alert_close_button', 'alert_ttl', 'user_id'], 'integer'],
            [['tab', '_user_email', 'alert_created', 'alert_url', 'alert_message', 'alert_view_type', 'alert_type', 'alert_action', 'alert_screen'], 'safe'],
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
        //$query = UserAlertsLog::find();
        $query = self::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email as _user_email")
            ->leftJoin('{{%users}} as t2', 't2.user_id = t1.user_id');

        if ($user_id) {
            $query->where(['t1.user_id' => $user_id]);
        }

        // add conditions that should always apply here

        if ($user_id) {
            $params = array_merge($_GET, ['tab' => 'alert-logs', '#' => 'alert-logs']);
        } else {
            $params = $_GET;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => $params,
                'sortParam' => 'sort-p7',
                'defaultOrder' => ['alert_created'=>SORT_DESC],
                'attributes' => [
                    'alert_created',
                    'alert_message',
                    'alert_view_type',
                    'alert_type',
                    'alert_action',
                    'alert_ttl',
                    'user_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                ]
            ],
            'pagination' => [
                'params' => $params,
                'pageParam' => 'p-al-inf',
                'pageSizeParam' => 'per-p-al-inf',
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
            //'alert_created' => $this->alert_created,
            'alert_close_button' => $this->alert_close_button,
            'alert_ttl' => $this->alert_ttl,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['ilike', 'alert_url', $this->alert_url])
            ->andFilterWhere(['ilike', 'alert_message', $this->alert_message])
            ->andFilterWhere(['ilike', 'alert_view_type', $this->alert_view_type])
            ->andFilterWhere(['ilike', 'alert_type', $this->alert_type])
            ->andFilterWhere(['ilike', 'alert_action', $this->alert_action]);

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->alert_created) && strpos($this->alert_created, '-') !== false) {
            $tmp = explode(' - ', $this->alert_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'alert_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }
}
