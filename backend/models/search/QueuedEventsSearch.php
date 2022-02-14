<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use common\models\QueuedEvents;

/**
 * QueuedEventsSearch represents the model behind the search form of `common\models\QueuedEvents`.
 */
class QueuedEventsSearch extends QueuedEvents
{
    public $_user_email;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_uuid', 'job_id', 'queue_id', 'job_type', 'job_status', /*'job_created', 'job_started', 'job_finished',*/ '_user_email'], 'safe'],
            [['node_id', 'user_id'], 'integer'],
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
     * @return array
     */
    public static function getQueuesIds()
    {
        $ret = self::getDb()->cache(
            function($db) {
                return self::find()
                    ->select('queue_id')
                    ->groupBy('queue_id')
                    ->orderBy('queue_id')
                    ->asArray()
                    ->all();
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'QueuedEvents'])
        );

        /*
        $ret = self::find()
            ->select('queue_id')
            ->groupBy('queue_id')
            ->orderBy('queue_id')
            ->asArray()
            ->all();
        */

        $arr = [];
        foreach ($ret as $v) {
            $arr[$v['queue_id']] = $v['queue_id'];
        }
        return $arr;
    }

    /**
     * @return array
     */
    public static function getQueuesStatuses()
    {
        $ids = self::getQueuesIds();
        $jobStatuses = self::queuedStatuses();
        $ret = [];
        foreach ($ids as $queue_k=>$queue_v) {

            /* инициируем начальные значения для всех очередей по каждому из статусов */
            foreach ($jobStatuses as $status_k=>$sstatus_v) {
                if (!isset($ret[$status_k][$queue_k])) { $ret[$status_k][$queue_k] = 0; }
            }

            unset($out);
            $cmd = (dirname(Yii::$app->basePath)) . DIRECTORY_SEPARATOR . "yii {$queue_k}/info";
            exec($cmd, $out);
            if (isset($out) && is_array($out)) {
                foreach ($out as $v) {

                    $v = trim($v);
                    if ($v !== '' && strtolower($v) !== 'jobs') {
                        $vv = explode(':', $v);
                        if (isset($vv[0], $vv[1])) {
                            $vv[0] = trim(str_replace('-', '', $vv[0]));
                            if (isset($ret[$vv[0]][$queue_k])) {
                                $ret[$vv[0]][$queue_k] = $vv[1];
                            }
                        }
                    }

                }
            }
        }

        /* первый вариант массива для возврата */
        //$ret;

        /* второй вариант массива для возврата */
        $ret2 = [];
        $i = 0;
        foreach ($ret as $k=>$v) {
            $ret2[$i]['status'] = $k;
            foreach ($v as $kk=>$vv) {
                $ret2[$i][$kk] = $vv;
            }
            $i++;
        }

        return [
            'v1' => $ret,
            'v2' => $ret2,
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public static function getQueuesStatusesDataProvider()
    {
        $ret = self::getQueuesStatuses();

        $dataProvider = new ArrayDataProvider([
            //'key' => 'id',
            'allModels' => $ret['v2'],
            /*
            'sort' => [
                'attributes' => ['name'],
            ],
            */
            'pagination' => false,
        ]);

        return $dataProvider;
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
        $query = self::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email")
            ->leftJoin('{{%users}} as t2', 't2.user_id = t1.user_id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['job_created'=>SORT_DESC],
                'attributes' => [
                    'job_id',
                    'queue_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                    'job_status',
                    'job_type',
                    'job_created',
                    'job_started',
                    'job_finished',
                ]
            ],
            'pagination' => [
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
            'node_id' => $this->node_id,
            'queue_id' => $this->queue_id,
            'user_id' => $this->user_id,
            //'job_created' => $this->job_created,
            //'job_started' => $this->job_started,
            //'job_finished' => $this->job_finished,
            'event_uuid' => $this->event_uuid,
            'job_id' => $this->job_id,
            'job_type' => $this->job_type,
            'job_status' => $this->job_status,
        ]);

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);

        return $dataProvider;
    }
}
