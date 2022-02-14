<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use common\helpers\Functions;
use common\models\UserPayments;

/**
 * UserPaymentsSearch represents the model behind the search form about common\models\UserPayments.
 */
class UserPaymentsSearch extends UserPayments
{
    public $_user_email;
    public $tab;

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['pay_id', 'user_id'], 'integer'],
            [['pay_amount', 'merchant_amount'], 'number'],
            [['tab', 'pay_date', 'merchant_updated', '_user_email', 'pay_type', 'pay_status', 'merchant_status'], 'safe'],
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
     * @param integer $user_id
     *
     * @return ActiveDataProvider
     */
    public function search($params, $user_id=null)
    {
        $query = self::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email")
            ->leftJoin('{{%users}} as t2', 't2.user_id = t1.user_id');

        if ($user_id) {
            $query->where(['t1.user_id' => $user_id]);
        }

        //$query = UserPayments::find()->with('user');
        // add conditions that should always apply here

        if ($user_id) {
            $params = array_merge($_GET, ['tab' => 'payment-logs', '#' => 'payment-logs']);
        } else {
            $params = $_GET;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => $params,
                'sortParam' => 'sort-p8',
                'defaultOrder' => ['pay_id'=>SORT_DESC],
                'attributes' => [
                    'pay_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                    'pay_amount',
                    'pay_type',
                    'pay_status',
                    'pay_date',
                    'merchant_amount',
                    'merchant_status',
                    'merchant_updated',
                ]
            ],
            'pagination' => [
                'params' => $params,
                'pageSize' => 100,
                //'route'=>'payments/index',
                'pageParam' => 'p-payments-inf',
                'pageSizeParam' => 'per-p-payments-inf',
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
            'pay_id' => $this->pay_id,
            //'user_id' => $this->user_id,
            't1.pay_type' => $this->pay_type,
            'pay_status' => $this->pay_status,
            'merchant_status' => $this->merchant_status,
            'pay_date' => $this->pay_date,
            'merchant_updated' => $this->merchant_updated,
            'pay_amount' => $this->pay_amount,
            //'users.user_email' => $this->,
        ]);

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);

        return $dataProvider;
    }

    /**
     * Function returns dataProvider (statistics for UserPayments)
     *
     * @return SqlDataProvider
     */
    public function totalStatistic()
    {
        $sql = "
            SELECT count(*) as cnt, 'day' as period, 1 as sortf
            FROM {{%user_payments}}
            WHERE (pay_date BETWEEN :today_begin AND :today_end)
            AND (pay_status = :pay_status)
              UNION
            SELECT count(*) as cnt, 'week' as period, 2 as sortf
            FROM {{%user_payments}}
            WHERE (pay_date BETWEEN :week_begin AND :week_end)
            AND (pay_status = :pay_status)
              UNION
            SELECT count(*) as cnt, 'mnth' as period, 3 as sortf
            FROM {{%user_payments}}
            WHERE (pay_date BETWEEN :mnth_begin AND :mnth_end)
            AND (pay_status = :pay_status)
            ORDER BY sortf
        ";

        $params = Functions::dateInfo();
        $params['pay_status'] = self::STATUS_PAID;

        $dataProvider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $params,
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
}
