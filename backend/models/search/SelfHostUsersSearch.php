<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\SelfHostUsers;
use common\models\ShuCheckLog;

/**
 * SelfHostUsersSearch represents the model behind the search form of `common\models\SelfHostUsers`.
 */
class SelfHostUsersSearch extends SelfHostUsers
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shu_id', 'shu_status', 'shu_role', 'shu_support_status', 'shu_brand_status', 'user_id', 'shu_business_status', 'license_mismatch'], 'integer'],
            [['shu_company', 'shu_name', 'shu_email', 'shu_created', 'shu_updated', 'license_expire', 'shu_license_last_check', 'shu_license_last_check_ip', 'shu_promo_code',], 'safe'],
            [['shu_support_cost', 'shu_brand_cost'], 'number'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SelfHostUsers::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['shu_id'=>SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 100,
                'route'=>'self-host-users/index',
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
            'shu_id' => $this->shu_id,
            'shu_updated' => $this->shu_updated,
            'shu_status' => $this->shu_status,
            'shu_support_status' => $this->shu_support_status,
            'shu_brand_status' => $this->shu_brand_status,
            'user_id' => $this->user_id,
            'shu_business_status' => $this->shu_business_status,
        ]);

        if ($this->shu_promo_code != 'show_not_null') {
            $query->andFilterWhere(['shu_promo_code' => $this->shu_promo_code]);
        } else {
            $query->andWhere('shu_promo_code IS NOT NULL');
        }

        $query->andFilterWhere(['ilike', 'shu_company', $this->shu_company])
            ->andFilterWhere(['ilike', 'shu_name', $this->shu_name])
            ->andFilterWhere(['ilike', 'shu_email', $this->shu_email])
            ->andFilterWhere(['ilike', 'shu_license_last_check_ip', $this->shu_license_last_check_ip]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->shu_created) && strpos($this->shu_created, '-') !== false) {
            $tmp = explode(' - ', $this->shu_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'shu_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        // do we have values? if so, add a filter to our query
        if(!empty($this->shu_license_last_check) && strpos($this->shu_license_last_check, '-') !== false) {
            $tmp = explode(' - ', $this->shu_license_last_check);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'shu_license_last_check',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }

    /**
     * @param integer $shu_id
     * @return ActiveDataProvider
     */
    public function searchShuCheckLog($shu_id)
    {
        $query = ShuCheckLog::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['record_id'=>SORT_DESC],
            ],
        ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'shu_id' => $shu_id,
        ]);

        $query->limit(20);

        return $dataProvider;
    }
}
