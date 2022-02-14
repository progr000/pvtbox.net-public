<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserPayments;

/**
 * UserPaymentsSearch represents the model behind the search form about common\models\UserPayments.
 *
 * @property integer $_notif_date_ts
 *
 */
class UserPaymentsSearch extends UserPayments
{
    public $_pay_date_ts;

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
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find()->where([
            'user_id'    => Yii::$app->user->identity->getId(),
            'pay_status' => UserPayments::STATUS_PAID,
        ]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['pay_date'=>SORT_DESC],
                'attributes' => [
                    'pay_date',
                ]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_pay_date_ts = strtotime($this->pay_date) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    }
}
