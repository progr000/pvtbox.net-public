<?php

namespace frontend\models\search;

use common\models\UserNode;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use common\models\UserServerLicenses;

/**
 * ColleaguesSearch represents the model behind the search form about common\models\UserColleagues.
 */
class ServerLicensesSearch extends UserServerLicenses
{

    public $user_own_node;
    public $node_id;
    public $user_id;
    public $is_server;
    public $node_online;
    public $node_devicetype;
    public $node_ostype;
    public $node_osname;
    public $node_country;
    public $node_city;
    public $node_status;
    public $user_email;
    public $node_name;
    public $node_disk_usage;
    public $node_download_speed;
    public $node_upload_speed;
    public $node_wipe_status;

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
     * @param int $business_admin_user_id
     * @return ActiveDataProvider
     */
    public function getListServerNodes($business_admin_user_id)
    {
        $sql = "SELECT
                  (CASE WHEN (t1.user_id = :business_admin_user_id) THEN 1 ELSE 0 END) as user_own_node,
                  t1.node_id,
                  t1.user_id,
                  t1.is_server,
                  t1.node_online,
                  t1.node_devicetype,
                  t1.node_ostype,
                  t1.node_osname,
                  t1.node_country,
                  t1.node_city,
                  t1.node_status,
                  t1.node_disk_usage,
                  t1.node_download_speed,
                  t1.node_upload_speed,
                  t1.node_wipe_status,
                  t3.user_email,
                  t1.node_name,
                  t2.lic_srv_id,
                  t2.lic_srv_start,
                  t2.lic_srv_end,
                  t2.lic_srv_period
                FROM {{%user_node}} as t1
                LEFT JOIN {{%user_server_licenses}} as t2 ON (t1.node_id = t2.lic_srv_node_id) AND (t2.lic_srv_owner_user_id = :business_admin_user_id)
                INNER JOIN {{%users}} as t3 ON t1.user_id = t3.user_id
                WHERE ((t1.user_id = :business_admin_user_id) OR (t1.user_id IN (
                  SELECT user_id FROM {{%user_colleagues}} WHERE (user_id!= :business_admin_user_id) AND (user_id IS NOT NULL) AND (collaboration_id IN (
                    SELECT collaboration_id FROM {{%user_collaborations}} WHERE user_id = :business_admin_user_id)
                  )
                )))
                AND (t1.is_server = :is_server)
                AND (t1.node_status NOT IN (:DELETED, :DEACTIVATED))
                AND NOT((t1.node_prev_status = :DEACTIVATED) AND (t1.node_status IN (:LOGGEDOUT, :WIPED)))
                ORDER BY user_own_node DESC, t3.user_email ASC, t2.lic_srv_node_id ASC NULLS LAST";

        $dataProvider = new SqlDataProvider([
            //'query' => $query,
            'sql' => $sql,
            'params' => [
                'business_admin_user_id' => $business_admin_user_id,
                'is_server'              => UserNode::IS_SERVER,
                'DELETED'                => UserNode::STATUS_DELETED,
                'DEACTIVATED'            => UserNode::STATUS_DEACTIVATED,
                'LOGGEDOUT'              => UserNode::STATUS_LOGGEDOUT,
                'WIPED'                  => UserNode::STATUS_WIPED,
            ],
            /*
            'sort'=> [
                'defaultOrder' => ['t1.colleague_email' => SORT_ASC],
                'attributes' => [
                    't1.colleague_id',
                    't1.colleague_email',
                    't1.colleague_permission',
                ]
            ],
            */
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);

        //$query->andFilterWhere(['like', 'colleague_user_email', $this->colleague_user_email]);

        return $dataProvider;
    }
}
