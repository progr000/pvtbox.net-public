<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserNode;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UserNodeSearch extends UserNode
{
    public $_user_email;
    public $tab;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'node_id',
                    'node_online',
                    'node_status',
                    'node_prev_status',
                    'node_disk_usage',
                    'node_logout_status',
                    'node_wipe_status',
                    'is_server',
                ], 'integer'
            ],
            [
                [
                    'tab',
                    'node_name',
                    'node_created',
                    'node_last_ip',
                    'node_useragent',
                    'node_osname',
                    'node_ostype',
                    'node_devicetype',
                    '_user_email',
                ], 'safe'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'node_id' => 'Id',
            'node_hash' => 'Hash',
            'node_name' => 'Name',
            'node_created' => 'Registered',
            'node_updated' => 'Last act.',
            'node_useragent' => 'UserAgent',
            'node_osname' => 'OS Name',
            'node_ostype' => 'OS Type',
            'node_devicetype' => 'Device',
            'node_last_ip' => 'Last IP',
            'node_countrycode' => 'Country',
            'node_country' => 'Country',
            'node_city' => 'City',
            'node_online' => 'Online',
            'node_status' => 'Status',
            'node_prev_status' => 'Prev<br />status',
            'node_upload_speed' => 'Dwn speed',
            'node_download_speed' => 'Up speed',
            'node_disk_usage' => 'Disk Usage',
            'node_logout_status' => 'Logout status',
            'node_wipe_status'   => 'Wipe status',
            'user_id' => 'User Id',
            'is_server' => 'Is server',
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
    public function search($params, $user_id=null)
    {
//        $query = self::find();
//            //->where(['user_id' => $user_id]);
//
//        if ($user_id) {
//            $query->where(['user_id' => $user_id]);
//        }

        $query = self::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email as _user_email")
            ->innerJoin('{{%users}} as t2', 't2.user_id = t1.user_id');

        if ($user_id) {
            $query->where(['t1.user_id' => $user_id]);
        } else {
//            if (!isset($_GET['showBrowsers'])) {
//                $query->where('t1.node_devicetype != :DEVICE_BROWSER', ['DEVICE_BROWSER' => self::DEVICE_BROWSER]);
//            }
        }

        if ($user_id) {
            $params = array_merge($_GET, ['tab' => 'node-info', '#' => 'node-info']);
        } else {
            $params = $_GET;
        }

        //var_dump($params);exit;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => $params,
                'sortParam' => 'sort-p1',
                'defaultOrder' => ['node_id'=>SORT_DESC],
                'attributes' => [
                    'node_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                    'node_status',
                    'node_prev_status',
                    'node_created',
                    'node_updated',
                    'node_ostype',
                    'node_devicetype',
                    'node_name',
                    'node_disk_usage',
                    'node_osname',
                    'node_online',
                    'is_server',
                ]
            ],
            'pagination' => [
                'params' => $params,
                'pageParam' => 'p-nd-inf',
                'pageSizeParam' => 'per-p-nd-inf',
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
            //'user_id'         => $this->user_id,
            'node_id'         => $this->node_id,
            'node_status'     => $this->node_status,
            //'node_created'    => $this->node_created,
            //'node_updated'    => $this->node_updated,
            'node_ostype'     => $this->node_ostype,
            //'node_devicetype' => $this->node_devicetype,
            'node_online'     => $this->node_online,
            //'node_name'       => $this->node_name,
            'is_server'       => $this->is_server,
        ]);


        if (!$this->node_devicetype && !$user_id) {
            $query->andWhere('t1.node_devicetype != :DEVICE_BROWSER', ['DEVICE_BROWSER' => self::DEVICE_BROWSER]);
        } else {
            $query->andFilterWhere(['node_devicetype' => $this->node_devicetype]);
        }


        $query->andFilterWhere(['like', 'node_name', $this->node_name]);
        $query->andFilterWhere(['like', 'node_osname', $this->node_osname]);

        if (($ip = ip2long($this->node_last_ip)) !== false) {
            $query->andFilterWhere(['node_last_ip' => $ip]);
        }

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);

        return $dataProvider;
    }

    /**
     * @param $user_id
     * @return array
     */
    public function selectCountNodes($user_id)
    {
        $ret = [
            'user_total_nodes'        => 0,
            'user_server_nodes'       => 0,
            'colleagues_total_nodes'  => 0,
            'colleagues_server_nodes' => 0,
        ];
        $query = "SELECT
                      count(*) as total_nodes,
                      sum(is_server) as server_nodes
                  FROM {{%user_node}}
                  WHERE (user_id = :user_id)
                  AND (node_status NOT IN (:STATUS_DELETED, :STATUS_DEACTIVATED, :STATUS_WIPED))
                  AND (node_devicetype != :DEVICE_BROWSER)
                  GROUP BY user_id; ";
        $res = Yii::$app->db->createCommand($query, [
            'user_id'            => $user_id,
            'STATUS_DELETED'     => UserNode::STATUS_DELETED,
            'STATUS_DEACTIVATED' => UserNode::STATUS_DEACTIVATED,
            'STATUS_WIPED'       => UserNode::STATUS_WIPED,
            'DEVICE_BROWSER'     => UserNode::DEVICE_BROWSER,
        ])->queryOne();
        if (is_array($res) && isset($res['total_nodes'], $res['server_nodes'])) {
            $ret['user_total_nodes']  = $res['total_nodes'];
            $ret['user_server_nodes'] = $res['server_nodes'];
        }

        unset($res);
        $query = "SELECT
                      count(*) as total_nodes,
                      sum(is_server) as server_nodes
                  FROM {{%user_node}}
                  WHERE (user_id IN (
                      SELECT lic_colleague_user_id
                      FROM {{%user_licenses}}
                      WHERE (lic_owner_user_id = :user_id)
                      AND (lic_colleague_user_id != :user_id)
                      AND (lic_colleague_user_id IS NOT NULL)
                  ))
                  AND (node_status NOT IN (:STATUS_DELETED, :STATUS_DEACTIVATED, :STATUS_WIPED))
                  AND (node_devicetype != :DEVICE_BROWSER)
                  GROUP BY user_id; ";
        $res = Yii::$app->db->createCommand($query, [
            'user_id'            => $user_id,
            'STATUS_DELETED'     => UserNode::STATUS_DELETED,
            'STATUS_DEACTIVATED' => UserNode::STATUS_DEACTIVATED,
            'STATUS_WIPED'       => UserNode::STATUS_WIPED,
            'DEVICE_BROWSER'     => UserNode::DEVICE_BROWSER,
        ])->queryOne();
        if (is_array($res) && isset($res['total_nodes'], $res['server_nodes'])) {
            $ret['colleagues_total_nodes']  = $res['total_nodes'];
            $ret['colleagues_server_nodes'] = $res['server_nodes'];
        }

        return $ret;
    }

    /**
     * @return array
     */
    public static function totalUserNodeInfo()
    {
        unset($res);
        $sql_online = "SELECT
                         count(*) as cnt,
                         node_online
                       FROM {{%user_node}}
                       WHERE node_devicetype != :DEVICE_BROWSER
                       GROUP BY node_online
                       ORDER BY node_online DESC;";

        $res = Yii::$app->db->createCommand($sql_online, [
            'DEVICE_BROWSER' => UserNode::DEVICE_BROWSER,
        ])->queryAll();

        $ret['online']['total'] = 0;
        $ret['online'][UserNode::ONLINE_ON] = 0;
        $ret['online'][UserNode::ONLINE_OFF] = 0;
        foreach ($res as $v) {
            $ret['online'][$v['node_online']] = $v['cnt'];
            $ret['online']['total'] += $v['cnt'];
        }

        unset($res);
        $sql_online = "SELECT
                         count(*) as cnt
                       FROM {{%user_node}}
                       WHERE (node_devicetype = :DEVICE_BROWSER)
                       AND (node_updated > :min_online_date)";
        $res = Yii::$app->db->createCommand($sql_online, [
            'min_online_date' => date(SQL_DATE_FORMAT, time() - UserNode::WebFMOnlineTimeout),
            'DEVICE_BROWSER' => UserNode::DEVICE_BROWSER,
        ])->queryOne();
        $ret['online']['OnLineBrowser'] = isset($res['cnt']) ? $res['cnt'] : 0;

        unset($res);
        $sql_devicetype = "SELECT
                             count(*) as cnt,
                             node_devicetype
                           FROM {{%user_node}}
                           WHERE node_devicetype != :DEVICE_BROWSER
                           GROUP BY node_devicetype
                           ORDER BY node_devicetype ASC;";
        $res = Yii::$app->db->createCommand($sql_devicetype, [
            'DEVICE_BROWSER' => UserNode::DEVICE_BROWSER,
        ])->queryAll();
        foreach ($res as $v) {
            $ret['devicetype'][$v['node_devicetype']] = $v['cnt'];
        }

        unset($res);
        $sql_statuses = "SELECT
                           count(*) as cnt,
                           node_status
                         FROM {{%user_node}}
                         WHERE node_devicetype != :DEVICE_BROWSER
                         GROUP BY node_status
                         ORDER BY node_status ASC;";
        $res = Yii::$app->db->createCommand($sql_statuses, [
            'DEVICE_BROWSER' => UserNode::DEVICE_BROWSER,
        ])->queryAll();
        foreach ($res as $v) {
            $ret['statuses'][$v['node_status']] = $v['cnt'];
        }

        unset($res);
        $ret['is_server'][UserNode::IS_SERVER]  = 0;
        $ret['is_server'][UserNode::NOT_SERVER] = 0;
        $sql_statuses = "SELECT
                           count(*) as cnt,
                           is_server
                         FROM {{%user_node}}
                         WHERE node_devicetype != :DEVICE_BROWSER
                         GROUP BY is_server
                         ORDER BY is_server DESC;";
        $res = Yii::$app->db->createCommand($sql_statuses, [
            'DEVICE_BROWSER' => UserNode::DEVICE_BROWSER,
        ])->queryAll();
        foreach ($res as $v) {
            $ret['is_server'][$v['is_server']] = $v['cnt'];
        }

        return $ret;
    }
}
