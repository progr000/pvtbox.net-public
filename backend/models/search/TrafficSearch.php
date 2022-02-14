<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\helpers\Json;
use common\helpers\Functions;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class TrafficSearch extends Model
{
    public static $logType = [
        'p2p',
        'turn',
        'websocket',
    ];

    public static function logNames()
    {
        return [
            'p2p'       => 'p2p',
            'turn'      => 'Turn',
            'websocket' => 'WebSocket',
        ];
    }

    public static function getLogName($logVariant)
    {
        $logs = self::logNames();
        if (isset($logs[$logVariant])) {
            return $logs[$logVariant];
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['log_id'], 'integer'],
            [
                [
                    'log_key',
                    'log_val',
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
     * @param string $logType
     * @param string $user_remote_hash
     * @return ActiveDataProvider
     */
    public function search_old($logType, $user_remote_hash)
    {

        $user_remote_hash = '017cddcbdff20d823af0ed87887190a20ed3624522b58925ae6a7ca7685ba85589a7bcdf759ce26527d7a010d8734c7c6f4d843713f1b0caab563ffb75af9c4c';

        if ($logType == self::$logType[0]) {
            $url = "http://ip2.2nat.biz:8086/query?q=SELECT+sum(rx_wd)+as+_in%2C+sum(tx_wd)+as+_out+FROM+%22session%3Aend%22+WHERE++uid%3D'{$user_remote_hash}'+AND+time+%3C%3D+now()+GROUP+BY+uid%2C+time(1d)&db=telegraf";
        } else if (($logType == self::$logType[1])) {
            $url = "http://ip2.2nat.biz:8086/query?q=SELECT+sum(rx_wr)+as+_in%2C+sum(tx_wr)+as+_out+FROM+%22session%3Aend%22+WHERE++uid%3D'{$user_remote_hash}'+AND+time+%3C%3D+now()+GROUP+BY+uid%2C+time(1d)&db=telegraf";
        } else {
            $url = "http://ip2.2nat.biz:8086/query?q=SELECT+sum(rx_ws)+as+_in%2C+sum(tx_ws)+as+_out+FROM+%22session%3Aend%22+WHERE++uid%3D'{$user_remote_hash}'+AND+time+%3C%3D+now()+GROUP+BY+uid%2C+time(1d)&db=telegraf";
        }

        $items = [];
        $res = Functions::HttpGet($url, Yii::$app->params['LogAuthUser'], Yii::$app->params['LogAuthPasswd']);
        if ($res) {
            $jsonres = Json::decode($res);
            if (is_array($jsonres) && isset($jsonres['results'][0]['series'][0]['columns'], $jsonres['results'][0]['series'][0]['values'])) {
                $columns = $jsonres['results'][0]['series'][0]['columns'];
                $values  = $jsonres['results'][0]['series'][0]['values'];
                foreach ($values as $k=>$v) {
                    //var_dump($v);exit;
                    foreach ($v as $kk=>$vv) {
                        $items[$k][$columns[$kk]] = ($vv = $vv ? $vv : 0);
                    }
                }
                foreach ($items as $k=>$v) {
                    $items[$k]['_total'] = $items[$k]['_in'] + $items[$k]['_out'];
                }
            }
        }

        $dataProvider = new ArrayDataProvider([
            //'key' => 'id',
            'allModels' => $items,
            /*
            'sort' => [
                'attributes' => ['name'],
            ],
            */
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        return $dataProvider;
    }

    /**
     * @param integer $user_id
     * @return SqlDataProvider
     */
    public function searchBD($user_id)
    {
        $query = "SELECT
                    date_trunc('day', record_created) as tmstmp,
                    sum(rx_wd) AS sum_rx_wd,
                    sum(tx_wd) AS sum_tx_wd,
                    sum(rx_wr) AS sum_rx_wr,
                    sum(tx_wr) AS sum_tx_wr,
                    sum(rx_wd) + sum(tx_wd) AS total_wd,
                    sum(rx_wr) + sum(tx_wr) AS total_wr,
                    sum(rx_wr) + sum(rx_wd) AS total_rx,
                    sum(tx_wr) + sum(tx_wd) AS total_tx
                  FROM {{%traffic_log}}
                  WHERE (user_id = :user_id)
                  GROUP BY tmstmp
                  ORDER BY tmstmp DESC";

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => ['user_id' => $user_id],
            /*
            'sort'=> [
                'attributes' => [
                    'cnt',
                    'period',
                ]
            ],
            */
            'pagination' => [
                'params' => array_merge($_GET, ['tab' => 'traffic-info', '#' => 'traffic-info']),
                'pageParam' => 'p-tr-inf',
                'pageSizeParam' => 'per-p-tr-inf',
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

    /**
     * @param integer $user_id
     * @param string $date
     * @param string $type
     * @return SqlDataProvider
     */
    public function showFilesForTraffic($user_id, $date, $type)
    {
        switch ($type) {
            case "rx_wd":
                $where = "AND (t1.rx_wd > 0)";
                break;
            case "tx_wd";
                $where = "AND (t1.tx_wd > 0)";
                break;
            case "rx_wr";
                $where = "AND (t1.rx_wr > 0)";
                break;
            case "tx_wr";
                $where = "AND (t1.tx_wr > 0)";
                break;
            case "total_wd";
                $where = "AND ((t1.tx_wd > 0) OR (t1.rx_wd > 0))";
                break;
            case "total_wr";
                $where = "AND ((t1.tx_wr > 0) OR (t1.rx_wr > 0))";
                break;
            case "total_tx";
                $where = "AND ((t1.tx_wr > 0) OR (t1.tx_wd > 0))";
                break;
            case "total_rx";
                $where = "AND ((t1.rx_wr > 0) OR (t1.rx_wd > 0))";
                break;
            default:
                $where = "";
        }

        $query = "SELECT
                    t3.file_name,
                    t2.event_uuid,
					t2.diff_file_uuid,
					t2.rev_diff_file_uuid,
					--(CASE WHEN ((t2.diff_file_uuid IS NOT NULL) AND (t2.rev_diff_file_uuid IS NOT NULL)) THEN 'file' ELSE 'patch' END) as type_of_data,
					(CASE WHEN (t1.event_uuid = t2.event_uuid) THEN 'file' ELSE 'patch' END) as type_of_data,
                    t1.record_created,
                    max(t1.rx_wd) as rx_wd,
                    max(t1.tx_wd) as tx_wd,
                    max(t1.rx_wr) as rx_wr,
                    max(t1.tx_wr) as tx_wr,
                    t1.user_id,
                    t1.node_id,
                    (CASE WHEN (t4.node_name IS NULL) THEN 'empty' ELSE t4.node_name END) as node_name,
                    t1.is_share
                  FROM {{%traffic_log}} as t1
                  LEFT JOIN {{%user_file_events}} as t2 ON (t1.event_uuid = t2.event_uuid) OR (t1.event_uuid = t2.diff_file_uuid) OR (t1.event_uuid = t2.rev_diff_file_uuid)
                  LEFT JOIN {{%user_files}} as t3 ON t2.file_id = t3.file_id
                  LEFT JOIN {{%user_node}} as t4 ON t1.node_id = t4.node_id
                  WHERE (t1.user_id = :user_id) {$where}
                  AND (record_created BETWEEN :date1 AND (date(:date1)::timestamp + interval '86399 seconds'))
                  GROUP BY t3.file_name, t1.event_uuid, t2.event_uuid, t1.record_created, t1.user_id, t1.node_id, t4.node_name, t1.is_share, t2.diff_file_uuid, t2.rev_diff_file_uuid
                  ORDER BY t1.record_created DESC";

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'params' => [
                'user_id' => $user_id,
                'date1'   => $date,
            ],
            'pagination' => false,
        ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param string $user_remote_hash
     * @return ActiveDataProvider
     */
    public function search($user_remote_hash)
    {
        //$user_remote_hash = '97a7d9961b4b95629c09eedd57ffd6b84c589f9ec5187fb16e907f35687e644f7fa709f471d003ca2e05261fff3ac7fb785e5776310458c95d754b3d471753e9';

        $query = "SELECT
                    sum(\"rx_wd\") AS \"sum_rx_wd\",
                    sum(\"tx_wd\") AS \"sum_tx_wd\",
                    sum(\"rx_wr\") AS \"sum_rx_wr\",
                    sum(\"tx_wr\") AS \"sum_tx_wr\",
                    sum(\"rx_ws\") AS \"sum_rx_ws\",
                    sum(\"tx_ws\") AS \"sum_tx_ws\"
                  FROM \"session:info\"
                  WHERE (\"uid\"='{$user_remote_hash}')
                  AND (time > now()-30d)
                  GROUP BY time(1d) FILL(0)
                  ORDER BY time DESC";

        $url = "http://ip2.2nat.biz:8086/query?pretty=true&db=telegraf&q=" . urlencode($query);
        $res = Functions::HttpGet($url, Yii::$app->params['LogAuthUser'], Yii::$app->params['LogAuthPasswd']);

        $items = [];
        if ($res) {
            $jsonres = Json::decode($res);
            if (is_array($jsonres) && isset($jsonres['results'][0]['series'][0]['columns'], $jsonres['results'][0]['series'][0]['values'])) {
                $columns = $jsonres['results'][0]['series'][0]['columns'];
                $values  = $jsonres['results'][0]['series'][0]['values'];
                foreach ($values as $k=>$v) {
                    //var_dump($v);exit;
                    foreach ($v as $kk=>$vv) {
                        $items[$k][$columns[$kk]] = ($vv = $vv ? $vv : 0);
                    }
                }
                foreach ($items as $k=>$v) {
                    $items[$k]['total_wd'] = $items[$k]['sum_rx_wd'] + $items[$k]['sum_tx_wd'];
                    $items[$k]['total_wr'] = $items[$k]['sum_rx_wr'] + $items[$k]['sum_tx_wr'];
                    $items[$k]['total_ws'] = $items[$k]['sum_rx_ws'] + $items[$k]['sum_tx_ws'];
                    $items[$k]['total_rx'] = $items[$k]['sum_rx_wd'] + $items[$k]['sum_rx_wr'] + $items[$k]['sum_rx_ws'];
                    $items[$k]['total_tx'] = $items[$k]['sum_tx_wd'] + $items[$k]['sum_tx_wr'] + $items[$k]['sum_tx_ws'];
                }
            }
        }

        $dataProvider = new ArrayDataProvider([
            //'key' => 'id',
            'allModels'  => $items,
            'sort'       => false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

}
