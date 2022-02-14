<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sessions;

/**
 * TransfersSearch represents the model behind the search form about common\models\Transfers.
 *
 * @property string $node_name
 * @property string $node_osname
 * @property string $node_ostype
 * @property string $node_devicetype
 * @property string $node_hash
 * @property integer $node_online
 * @property integer $node_logout_status
 * @property integer $node_wipe_status
 * @property integer $sess_created_ts
 */
class SessionsSearch extends Sessions
{
    public $node_name;
    public $node_osname;
    public $node_ostype;
    public $node_devicetype;
    public $node_hash;
    public $node_online;
    public $node_logout_status;
    public $node_wipe_status;
    public $sess_created_ts;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sess_id', 'user_id'], 'integer'],
            [['sess_ip', 'sess_countrycode', 'sess_country', 'sess_city', 'sess_useragent', 'sess_created'], 'safe'],
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
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /*
        Выбраь только три последних записи по каждой ноде:
        SELECT t1.sess_id, t1.node_id, COUNT(*) num, t1.sess_created
        FROM {{%sessions}} t1
        INNER JOIN {{%sessions}} t2	ON t1.node_id = t2.node_id AND t1.sess_id >= t2.sess_id
        WHERE t1.user_id=5420
        GROUP BY t1.node_id, t1.sess_id
        HAVING COUNT (*) <= 3
        --ORDER BY t1.sess_id, t1.node_id
        ORDER BY t1.sess_created

        ИЛИ такой вариант запроса

        --SELECT * FROM (
        SELECT
        node_id,
        sess_id,
        --ROW_NUMBER() OVER(ORDER BY node_id) num,
        RANK() OVER(PARTITION BY node_id ORDER BY sess_id) rnk
        --DENSE_RANK() OVER(ORDER BY node_id) rnk_dense
        FROM {{%sessions}} as t1
        WHERE user_id=5420
        --) as dd WHERE rnk<=3
        --ORDER BY node_id
        */
        $query = self::find()
            ->alias('t1')
            ->select([
                't1.*',
                't2.node_name',
                't2.node_osname',
                't2.node_ostype',
                't2.node_devicetype',
                't2.node_hash',
                't2.node_online',
                't2.node_logout_status',
                't2.node_wipe_status',
            ])
            ->innerJoin('{{%user_node}} as t2', 't1.node_id = t2.node_id');
            //->leftJoin('{{%user_node}} as t2', 't1.node_id = t2.node_id');
            //->asArray()
            //->all();

        //$query = self::find()->joinWith(['node']);
        $query->andFilterWhere($params);
        //$query->addOrderBy(['sess_created'=>SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,

            'sort'=> [
                'defaultOrder' => ['sess_created'=>SORT_DESC],
                'attributes' => [
                    'sess_id',
                    //'sess_useragent',
                    //'sess_ip',
                    'sess_country',
                    'sess_city',
                    //'sess_action',
                    'sess_created',
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
                //'route' => '/user/profile?tab=2',
            ],
        ]);

        return $dataProvider;
    }

    /**
     * @param array $params
     * @param integer $limit
     * @return ActiveDataProvider
     */
    public function search2($params, $limit)
    {
        $query = self::find()
            ->select('*, extract(epoch from sess_created) as sess_created_ts')
            ->where($params)
            ->orderBy(['sess_created' => SORT_DESC])
            ->limit($limit);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,

            'sort'=> [
                'defaultOrder' => ['sess_created'=>SORT_DESC],
                'attributes' => [
                    'sess_id',
                    //'sess_useragent',
                    //'sess_ip',
                    'sess_country',
                    'sess_city',
                    //'sess_action',
                    'sess_created',
                ]
            ],

            'pagination' => false,
            /*
            'pagination' => [
                'pageSize' => $limit,
                //'route' => '/user/profile?tab=2',
            ],*/

        ]);

        return $dataProvider;
    }

    /**
     * @param integer $sess_action
     * @return array
     */
    public static function getStatusSessAction($sess_action)
    {
        switch ($sess_action) {
            case Sessions::ACTION_LOGIN:
                return ['text' => 'Signed In', 'class' => 'table-color-green'];
                break;
            case Sessions::ACTION_LOGOUT:
                return ['text' => 'Signed Out', 'class' => 'table-color-gray'];
                break;
            case Sessions::ACTION_REGISTER:
                return ['text' => 'Registered', 'class' => 'table-color-green'];
                break;
            default:
                return ['text' => 'Signed In', 'class' => 'table-color-green'];
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->sess_created_ts = strtotime($this->sess_created) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    }

}
