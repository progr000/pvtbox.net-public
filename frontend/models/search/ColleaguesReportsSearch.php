<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\ColleaguesReports;
use common\models\UserColleagues;

/**
 * TiketsSearch represents the model behind the search form about common\models\Tikets.
 */
class ColleaguesReportsSearch extends ColleaguesReports
{

    public $created_at_range;
    public $from_date;
    public $to_date;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $ret = parent::rules();
        $ret[] = [['created_at_range', 'from_date', 'to_date'], 'safe'];

        return $ret;
    }

    public function attributeLabels()
    {
        $ret = parent::attributeLabels();
        $ret['colleague_user_email'] = Yii::t('forms/colleague-reports-search', '--colleague_user_email');
    }

    /**
     * Creates data provider instance with search query applied
     * @param array|null $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /**
        SELECT
            max(report_date) as report_date,
            concat(
                extract(YEAR FROM report_date), '-',
                extract(MONTH FROM report_date), '-',
                extract(DAY FROM report_date), '-',
                extract(HOUR FROM report_date), '-',
                extract(MINUTE FROM report_date)
            ) as group_date_field,
            CASE WHEN extract(SECOND FROM report_date) > 15 THEN 2
                WHEN extract(SECOND FROM report_date) > 30 THEN 3
                WHEN extract(SECOND FROM report_date) > 45 THEN 4
                ELSE 1
            END as group_seconds_field,
            report_isnew,
            file_id,
            file_parent_id,
            file_parent_id_before_event,
            file_name_after_event,
            file_name_before_event,
            parent_folder_name_after_event,
            parent_folder_name_before_event,
            file_renamed,
            file_moved,
            is_folder,
            event_type,
            owner_user_id,
            colleague_user_id,
            colleague_user_email,
            colleague_node_id
        FROM {{%colleagues_reports}} WHERE owner_user_id=5420
        GROUP BY
            group_date_field,
            group_seconds_field,
            report_isnew,
            file_id,
            file_parent_id,
            file_parent_id_before_event,
            file_name_after_event,
            file_name_before_event,
            parent_folder_name_after_event,
            parent_folder_name_before_event,
            file_renamed,
            file_moved,
            is_folder,
            event_type,
            owner_user_id,
            colleague_user_id,
            colleague_user_email,
            colleague_node_id
         */
        $query = self::find()
            ->where(['owner_user_id' => Yii::$app->user->identity->getId()]);

        // add conditions that should always apply here

        if (!isset($params['tab']) || $params['tab'] != 3) { $params['tab'] = 3; }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['report_date'=>SORT_DESC],
                'attributes' => [
                    'report_date',
                    'colleague_user_email',
                ]
            ],
            'pagination' => [
                'params' => $params,
                'pageSize' => 50,
            ],

        ]);

        //var_dump($params);
        if ($params) {
            $this->load($params);
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        /*
        $query->andFilterWhere([
            'colleague_user_email' => $this->colleague_user_email,
        ]);
        */
        $query->andFilterWhere(['like', 'colleague_user_email', $this->colleague_user_email]);
        $query->andFilterWhere(['event_type' => $this->event_type]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->created_at_range) && strpos($this->created_at_range, '-') !== false) {
            $tmp = explode(' - ', $this->created_at_range);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date   = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'report_date',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date)) - Yii::$app->session->get('UserTimeZoneOffset', 0)),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date)) - Yii::$app->session->get('UserTimeZoneOffset', 0)),
                ]);
            }
        }

        return $dataProvider;
    }


    public static function getAllColleaguesDropDownList($user_id)
    {
        $query = "SELECT
                    lic_colleague_email as colleague_email,
                    (CASE WHEN (lic_colleague_user_id = lic_owner_user_id) THEN 1 ELSE 0 END) as is_owner
                  FROM {{%user_licenses}}
                  WHERE (lic_owner_user_id = :user_id)
                  AND (lic_colleague_email IS NOT NULL)
                  ORDER BY is_owner DESC, colleague_email ASC";

        $query = "SELECT DISTINCT ON (t1.colleague_email, is_owner)
                  t1.colleague_email,
                  (CASE WHEN (t1.colleague_permission = :PERMISSION_OWNER) THEN 1 ELSE 0 END) as is_owner
                FROM {{%user_colleagues}} as t1
                INNER JOIN {{%user_collaborations}} as t2 ON t1.collaboration_id = t2.collaboration_id
                WHERE (t2.user_id = :user_id)
                AND (t1.colleague_status != :STATUS_QUEUED_DEL)
                ORDER BY is_owner DESC, t1.colleague_email ASC";

        $ret = Yii::$app->db->createCommand($query, [
            'user_id'           => $user_id,
            'STATUS_QUEUED_DEL' => UserColleagues::STATUS_QUEUED_DEL,
            'PERMISSION_OWNER'  => UserColleagues::PERMISSION_OWNER,
        ])->queryAll();
        return $ret;
    }

    /**
     * @return integer
     */
    public static function countNewReports()
    {
        return self::find()
            ->andWhere('(owner_user_id=:owner_user_id) AND (report_isnew=:report_isnew)', [
                'owner_user_id' => Yii::$app->user->identity->getId(),
                'report_isnew'  => self::IS_NEW,
            ])
            ->count();
    }

    /**
     * @return int
     */
    public static function setReportsAsRead()
    {
        return self::updateAll(['report_isnew' => self::IS_OLD], ['owner_user_id' => Yii::$app->user->identity->getId()]);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_report_date_ts = strtotime($this->report_date) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    }
}
