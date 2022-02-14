<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\Mailq;

/**
 * MailqSearch represents the model behind the search form of `common\models\Mailq`.
 */
class MailqSearch extends Mailq
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mail_id', 'user_id', 'node_id'], 'integer'],
            [['mail_created', 'mail_from', 'mail_to', 'mail_reply_to', 'mail_subject', 'mail_body', 'mailer_letter_id', 'mailer_answer', 'mailer_description', 'mailer_letter_status', 'template_key', 'remote_ip'], 'safe'],
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
        $query = Mailq::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['mail_created'=>SORT_DESC],
                'attributes' => [
                    'mail_created',
                    'template_key',
                    'mail_from',
                    'mail_to',
                    'mail_reply_to',
                    'mailer_letter_status',
                    'remote_ip',
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
            'mail_id' => $this->mail_id,
            //'mail_created' => $this->mail_created,
            'user_id' => $this->user_id,
            'node_id' => $this->node_id,
            'mailer_letter_status' => $this->mailer_letter_status,
        ]);

        if (($ip = ip2long($this->remote_ip)) !== false) {
            $query->andFilterWhere(['remote_ip' => $ip]);
        }

        $query->andFilterWhere(['ilike', 'mail_from', $this->mail_from])
            ->andFilterWhere(['ilike', 'mail_to', $this->mail_to])
            ->andFilterWhere(['ilike', 'mail_reply_to', $this->mail_reply_to])
            ->andFilterWhere(['ilike', 'mail_subject', $this->mail_subject])
            ->andFilterWhere(['ilike', 'mail_body', $this->mail_body])
            ->andFilterWhere(['ilike', 'mailer_letter_id', $this->mailer_letter_id])
            ->andFilterWhere(['ilike', 'mailer_answer', $this->mailer_answer])
            //->andFilterWhere(['ilike', 'mailer_letter_status', $this->mailer_letter_status])
            ->andFilterWhere(['ilike', 'template_key', $this->template_key]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->mail_created) && strpos($this->mail_created, '-') !== false) {
            $tmp = explode(' - ', $this->mail_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'mail_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }

    /**
     * @return array
     */
    public static function getTotal()
    {
        $list = self::mailqStatuses();
        $arr = [];
        foreach ($list as $k=>$v) {
            $arr[$k] = 0;
        }

        $query = "SELECT
                    mailer_letter_status,
                  count(*) as cnt
                  FROM dl_mailq
                  GROUP BY mailer_letter_status";
        $res = Yii::$app->db->createCommand($query)->queryAll();
        foreach ($res as $v) {
            $key = $v['mailer_letter_status'];
            $cnt = intval($v['cnt']);
            if (isset($arr[$key])) {
                $arr[$key] += $cnt;
            } else {
                $arr[$key] = $cnt;
            }
        }

        return $arr;
    }
}
