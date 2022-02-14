<?php

namespace backend\models\search;

use backend\models\Admins;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use common\helpers\Functions;
use common\models\Users;
use common\models\UserFiles;
use common\models\UserNode;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UsersSearch extends Users
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_status', 'user_ref_id'], 'integer'],
            [
                [
                    'user_name',
                    'user_email',
                    'user_balance',
                    'user_last_ip',
                    'auth_key',
                    'password_hash',
                    'password_reset_token',
                    'user_created',
                    'user_updated',
                    'license_type',
                    'user_promo_code',
                ], 'safe'
            ],
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
     * @param \backend\models\Admins $Admin
     *
     * @return ActiveDataProvider
     */
    public function search($params, $Admin)
    {
        $query = Users::find();

        if ($Admin->admin_role != Admins::ROLE_ROOT) {
            $query->where(['user_ref_id' => $Admin->admin_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['user_id'=>SORT_DESC],
                'attributes' => [
                    'user_id',
                    'user_email',
                    'user_balance',
                    'user_status',
                    'user_created',
                    'user_updated',
                    'license_type',
                    'user_ref_id',
                ]
            ],
            'pagination' => [
                'pageSize' => 100,
                'route'=>'users/index',
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'user_status' => $this->user_status,
            //'user_created' => $this->user_created,
            //'user_updated' => $this->user_updated,
            'user_balance' => $this->user_balance,
            'license_type' => $this->license_type,
            'user_ref_id'  => $this->user_ref_id,
        ]);

        if ($this->user_promo_code != 'show_not_null') {
            $query->andFilterWhere(['user_promo_code' => $this->user_promo_code]);
        } else {
            $query->andWhere('user_promo_code IS NOT NULL');
        }

        $query->andFilterWhere(['like', 'user_name', $this->user_name])
              ->andFilterWhere(['like', 'user_email', $this->user_email]);
              //->andFilterWhere(['like', 'user_last_ip', $this->user_last_ip])

        if (($ip = ip2long($this->user_last_ip)) !== false) {
            $query->andFilterWhere(['user_last_ip' => $ip]);
        }

        // do we have values? if so, add a filter to our query
        if(!empty($this->user_created) && strpos($this->user_created, '-') !== false) {
            $tmp = explode(' - ', $this->user_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'user_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        // do we have values? if so, add a filter to our query
        if(!empty($this->user_updated) && strpos($this->user_updated, '-') !== false) {
            $tmp = explode(' - ', $this->user_updated);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'user_updated',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }

    /**
     * Function returns dataProvider (statistics for Users)
     *
     * @return SqlDataProvider
     */
    public function totalStatistic()
    {
        $sql = "
            SELECT count(*) as cnt, 'day' as period, 1 as sortf
            FROM {{%users}}
            WHERE user_created BETWEEN :today_begin AND :today_end
              UNION
            SELECT count(*) as cnt, 'week' as period, 2 as sortf
            FROM {{%users}}
            WHERE user_created BETWEEN :week_begin AND :week_end
              UNION
            SELECT count(*) as cnt, 'mnth' as period, 3 as sortf
            FROM {{%users}}
            WHERE user_created BETWEEN :mnth_begin AND :mnth_end
            ORDER BY sortf
        ";

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => Functions::dateInfo(),
            /*
            'sort'=> [
                'attributes' => [
                    'cnt',
                    'period',
                ]
            ],
            */
            'pagination' => false,
        ]);

        return $dataProvider;
    }

    /**
     * @return SqlDataProvider
     */
    public function activityStatistic()
    {
        $sql = "
            SELECT count(*) as cnt, 'now' as period, 1 as sortf
            FROM {{%users}}
            WHERE user_updated BETWEEN :_15minago AND :now
              UNION
            SELECT count(*) as cnt, 'for24hours' as period, 2 as sortf
            FROM {{%users}}
            WHERE user_updated BETWEEN :_24hoursago AND :now
              UNION
            SELECT count(*) as cnt, 'week' as period, 3 as sortf
            FROM {{%users}}
            WHERE user_updated BETWEEN :week_begin AND :now
            ORDER BY sortf
        ";

        $tmp = Functions::dateInfo();

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [
                'now'         => date(SQL_DATE_FORMAT, time()),
                '_15minago'   => date(SQL_DATE_FORMAT, time() - 15*60),
                '_24hoursago' => date(SQL_DATE_FORMAT, time() - 24*60*60),
                'week_begin'  => $tmp['week_begin'],
            ],
            'pagination' => false,
        ]);

        return $dataProvider;
    }

    /**
     * @return SqlDataProvider
     */
    public function sharesAndCollaborationsStatistic()
    {
        $sql = "SELECT
                  'Total shares' as field,
                  'PrefHiddenTotalSharesCount' as pref_key,
                  count(*) as cnt,
                  1 as sortf
                FROM {{%user_files}}
                WHERE is_shared = :FILE_SHARED
                  UNION ALL
                SELECT
                  'Total collaborations' as field,
                  'PrefHiddenTotalCollaborationsCount' as pref_key,
                  count(*) as cnt,
                  2 as sortf
                FROM {{%user_collaborations}} as t1
                INNER JOIN {{%user_files}} as t2 ON (t1.file_uuid=t2.file_uuid) AND (t1.user_id=t2.user_id)
                WHERE  t2.file_id IS NOT NULL
                ORDER BY sortf ASC
                ";

        return Yii::$app->db->createCommand($sql, [
            'FILE_SHARED'       => UserFiles::FILE_SHARED,
        ])->queryAll();
        /*
        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [
                'FILE_SHARED'       => UserFiles::FILE_SHARED,
                'FILE_COLLABORATED' => UserFiles::FILE_COLLABORATED,
            ],
            'pagination' => false,
        ]);

        return $dataProvider;
        */
    }

    /**
     * @return array
     */
    public static function totalUsersInfo()
    {
        unset($res);
        $sql_online = "SELECT
                         count(*) as cnt,
                         max(online) as online
                       FROM (

                            SELECT
                              user_id,
                              (CASE WHEN (max(node_updated) > :min_online_date) THEN :ONLINE_ON::INTEGER ELSE :ONLINE_OFF::INTEGER END) AS online
                            FROM {{%user_node}}
                            WHERE (node_devicetype = :DEVICE_BROWSER)
                            GROUP BY user_id

                            UNION

                            SELECT
                              user_id,
                              max(node_online) AS online
                            FROM {{%user_node}}
                            WHERE (node_devicetype != :DEVICE_BROWSER)
                            GROUP BY user_id

                       ) as t1
                       GROUP BY t1.online
                       ORDER BY online DESC;";

        $res = Yii::$app->db->createCommand($sql_online, [
            'min_online_date' => date(SQL_DATE_FORMAT, time() - UserNode::WebFMOnlineTimeout),
            'ONLINE_ON'  => UserNode::ONLINE_ON,
            'ONLINE_OFF' => UserNode::ONLINE_OFF,
            'DEVICE_BROWSER' => UserNode::DEVICE_BROWSER,
        ])->queryAll();

        $ret['online']['total'] = 0;
        $ret['online'][UserNode::ONLINE_ON] = 0;
        $ret['online'][UserNode::ONLINE_OFF] = 0;
        foreach ($res as $v) {
            $ret['online'][$v['online']] = $v['cnt'];
            $ret['online']['total'] += $v['cnt'];
        }

        unset($res);
        $sql_licenses = "SELECT
                           count(*) as cnt,
                           license_type
                         FROM {{%users}}
                         GROUP BY license_type;";
        $res = Yii::$app->db->createCommand($sql_licenses)->queryAll();
        foreach ($res as $v) {
            $ret['licenses'][$v['license_type']] = $v['cnt'];
        }

        unset($res);
        $sql_licenses = "SELECT
                           count(*) as cnt,
                           user_status
                         FROM {{%users}}
                         GROUP BY user_status;";
        $res = Yii::$app->db->createCommand($sql_licenses)->queryAll();
        foreach ($res as $v) {
            $ret['statuses'][$v['user_status']] = $v['cnt'];
        }


        return $ret;
    }
}
