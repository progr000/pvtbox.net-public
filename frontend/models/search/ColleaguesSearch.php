<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use common\models\UserColleagues;
use common\models\UserFiles;

/**
 * ColleaguesSearch represents the model behind the search form about common\models\UserColleagues.
 */
class ColleaguesSearch extends UserColleagues
{

    public $owner_user_id;
    public $colleague_user_id;
    public $collaboration_id;
    public $file_id;
    public $file_name;
    public $file_uuid;
    public $file_parent_id;
    public $awaiting_permissions;
    public $license_type;

    public $_colleague_invite_date_ts;
    public $_colleague_joined_date_ts;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_user_id', 'colleague_id', 'awaiting_permissions'], 'integer'],
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
     * @return ActiveDataProvider
     */
    public function getListColleagues()
    {
        $sql = "SELECT * FROM get_all_collaborated_colleagues(:user_id)";

        $dataProvider = new SqlDataProvider([
            //'query' => $query,
            'sql' => $sql,
            'params' => [
                'user_id' => $this->owner_user_id,
                //'STATUS_QUEUED_DEL' => UserColleagues::STATUS_QUEUED_DEL,
                //'PERMISSION_OWNER'  => UserColleagues::PERMISSION_OWNER,
                //'STATUS_JOINED'     => UserColleagues::STATUS_JOINED,
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
                'pageSize' => 7,
            ],
        ]);

        //$query->andFilterWhere(['like', 'colleague_user_email', $this->colleague_user_email]);

        return $dataProvider;
    }

    /**
     * @return \common\models\UserColleagues | array
     */
    public function getColleagueInfo()
    {
        $ret = self::find()
            ->alias('t1')
            ->select([
                't1.*',
            ])
            ->innerJoin('{{%user_collaborations}} as t2', '(t1.collaboration_id = t2.collaboration_id)')
            ->where('(t1.colleague_email=:colleague_email) AND (t2.user_id = :user_id)', [
                'user_id'         => $this->owner_user_id,
                'colleague_email' => $this->colleague_email,
            ])->one();

        if ($ret && $ret->user_id == $this->owner_user_id) {
            return null;
        }

        if (!$ret) {

            $query = "SELECT * FROM get_all_collaborated_colleagues(:owner_user_id)
                      WHERE colleague_email = :colleague_email
                      AND user_id != :owner_user_id";

            $ret = Yii::$app->db->createCommand($query, [
                'owner_user_id'    => $this->owner_user_id,
                'colleague_email'  => $this->colleague_email,
                //'PERMISSION_OWNER' => UserColleagues::PERMISSION_OWNER,
                //'STATUS_JOINED'    => UserColleagues::STATUS_JOINED,
            ])->queryOne();
        }

        return $ret;
    }

    /**
     * @return ArrayDataProvider
     */
    public function getColleagueListFolder()
    {
        /*
        SELECT
            t1.colleague_id,
            t1.colleague_email,
            t1.colleague_status,
            t1.colleague_permission,
            t1.user_id as colleague_user_id,
            t2.collaboration_id,
            t2.user_id as owner_user_id,
            t3.file_id,
            t3.file_parent_id,
            t3.file_uuid,
            t3.file_name
        FROM {{%user_colleagues}} t1
        INNER JOIN {{%user_collaborations}} t2 ON t1.collaboration_id = t2.collaboration_id
        INNER JOIN {{%user_files}} t3 ON (t2.file_uuid = t3.file_uuid) AND (t2.user_id=t3.user_id)
        WHERE (t1.colleague_email='user224@mail.ru') AND (t2.user_id = 121) AND (t3.is_deleted=0)
        */
        /*
        $res = self::find()
            ->alias('t1')
            ->select([
                't1.colleague_id',
                't1.colleague_email',
                't1.colleague_status',
                't1.colleague_permission',
                't1.colleague_invite_date',
                't1.colleague_joined_date',
                't1.user_id as colleague_user_id',
                't2.collaboration_id',
                't2.user_id as owner_user_id',
                't3.file_id',
                't3.file_parent_id',
                't3.file_uuid',
                't3.file_name',
            ])
            ->innerJoin('{{%user_collaborations}} as t2', '(t1.collaboration_id = t2.collaboration_id)')
            ->innerJoin('{{%user_files}} as t3', '((t2.file_uuid = t3.file_uuid) AND (t2.user_id = t3.user_id))')
            ->where('(t1.colleague_email=:colleague_email) AND (t2.user_id = :user_id) AND (t3.is_deleted=0)', [
                'user_id'         => $this->owner_user_id,
                'colleague_email' => $this->colleague_email,
                UserFiles::FILE_UNDELETED
            ])
            ->orderBy(['t3.file_name' => SORT_ASC])
            ->asArray()->all();

        */
        $query = "SELECT
                    t1.colleague_id,
                    t1.colleague_email,
                    t1.colleague_status,
                    t1.colleague_permission,
                    t1.colleague_invite_date,
                    t1.colleague_joined_date,
                    t1.user_id as colleague_user_id,
                    t2.collaboration_id,
                    t2.user_id as owner_user_id,
                    t3.file_id,
                    t3.file_parent_id,
                    t3.file_uuid,
                    t3.file_name as file_name,
                    1 as is_owner
                  FROM {{%user_colleagues}} as t1
                  INNER JOIN {{%user_collaborations}} as t2 ON (t1.collaboration_id = t2.collaboration_id)
                  INNER JOIN {{%user_files}} as t3 ON ((t2.file_uuid = t3.file_uuid) AND (t2.user_id = t3.user_id))
                  WHERE (t1.colleague_email = :colleague_email)
                  AND (t2.user_id = :user_id)
                  AND (t3.is_deleted = :FILE_UNDELETED)

                  UNION

                  SELECT
                    t1.colleague_id,
                    --t1.colleague_email,
                    t3.user_email as colleague_email,
                    t1.colleague_status,
                    t1.colleague_permission,
                    t1.colleague_invite_date,
                    t1.colleague_joined_date,
                    t1.user_id as colleague_user_id,
                    t2.collaboration_id,
                    t2.user_id as owner_user_id,
                    t4.file_id,
                    t4.file_parent_id,
                    t4.file_uuid,
                    t4.file_name as file_name,
                    0 as is_owner
                  FROM dl_user_colleagues as t1
                  INNER JOIN dl_user_collaborations as t2 ON t1.collaboration_id = t2.collaboration_id
                  INNER JOIN dl_users as t3 ON t2.user_id = t3.user_id
                  INNER JOIN dl_user_files as t4 ON ((t2.file_uuid = t4.file_uuid) AND (t3.user_id = t4.user_id))
                  WHERE (t1.user_id = :user_id)
                  AND (t1.colleague_permission != :PERMISSION_OWNER)
                  AND (t1.colleague_status = :STATUS_JOINED)
                  AND (t2.user_id != :user_id)
                  AND (t3.user_email = :colleague_email)
                  AND (t4.is_deleted = :FILE_UNDELETED)

                  ORDER BY file_name
                  ";
        $res = Yii::$app->db->createCommand($query, [
            'user_id'          => $this->owner_user_id,
            'colleague_email'  => $this->colleague_email,
            'FILE_UNDELETED'   => UserFiles::FILE_UNDELETED,
            'STATUS_JOINED'    => UserColleagues::STATUS_JOINED,
            'PERMISSION_OWNER' => UserColleagues::PERMISSION_OWNER,
        ])->queryAll();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $res,
            /*
            'sort'=> [
                'defaultOrder' => ['t3.file_name' => SORT_ASC],
                'attributes' => [
                    't3.file_name',
                    //'t3.file_id',
                ]
            ],
            */
            /*
            'pagination' => [
                'pageSize' => 20,
            ],
            */
        ]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getAvailableFolderList()
    {
        $query = "SELECT
            t4.file_id,
            t4.file_uuid,
            t4.file_name,
            t4.file_parent_id,
            t4.is_collaborated
        FROM {{%user_files}} as t4
        LEFT JOIN {{%user_collaborations}} as t2 ON (t4.file_uuid = t2.file_uuid) AND (t2.user_id = t4.user_id)
        WHERE (t4.user_id = :user_id)
        AND (t4.file_parent_id = :ROOT_PARENT_ID)
        AND (t4.is_folder = :TYPE_FOLDER)
        AND (t4.is_deleted != :FILE_DELETED)
        AND (t4.is_owner = :IS_OWNER)
        AND (
          (t2.collaboration_id NOT IN (
                SELECT collaboration_id
                FROM {{%user_colleagues}}
                WHERE (colleague_email = :colleague_email)
                AND (colleague_status IN (:STATUS_INVITED, :STATUS_JOINED, :STATUS_QUEUED_ADD))
          ))
          OR (t2.collaboration_id IS NULL)
        )
        ORDER BY t4.file_name ASC";

        $res = Yii::$app->db->createCommand($query, [
            'colleague_email'   => $this->colleague_email,
            'user_id'           => Yii::$app->user->identity->getId(),
            'TYPE_FOLDER'       => UserFiles::TYPE_FOLDER,
            'ROOT_PARENT_ID'    => UserFiles::ROOT_PARENT_ID,
            'FILE_DELETED'      => UserFiles::FILE_DELETED,
            'IS_OWNER'          => UserFiles::IS_OWNER,
            'STATUS_INVITED'    => UserColleagues::STATUS_INVITED,
            'STATUS_JOINED'     => UserColleagues::STATUS_JOINED,
            'STATUS_QUEUED_ADD' => UserColleagues::STATUS_QUEUED_ADD,
        ])->queryAll();
        //var_dump($res); exit;

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_colleague_joined_date_ts = strtotime($this->colleague_joined_date) + Yii::$app->session->get('UserTimeZoneOffset', 0);
        $this->_colleague_invite_date_ts = strtotime($this->colleague_invite_date) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    }
}
