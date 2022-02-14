<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\UserCollaborations;
use common\models\UserFiles;

/**
 * SharesAndCollaborationsSearch represents the model behind the search form of `common\models\UserFiles`.
 */
class SharesAndCollaborationsSearch extends UserFiles
{
    public $_user_email;
    public $_file_name;

    public $collaboration_created;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        /*
        $ret = parent::rules();
        $ret[] = [['_user_email'], 'safe'];
        $ret[] = [['file_id'], 'integer'];
        $ret[] = [['share_hash', 'share_group_hash'], 'safe'];
        return $ret;
        */
        return [
            [
                [
                    'file_id',
                    //'file_size',
                    'collaboration_id',
                    'is_collaborated',
                    'is_shared',
                    'is_folder',
                    'is_deleted',
                    'user_id',
                    'node_id',
                    'file_lastatime',
                    'collaboration_id',
                ], 'integer'
            ],
            [
                [
                    'file_uuid',
                    'file_name',
                    'file_created',
                    'file_updated',
                    'share_created',
                    'share_hash',
                    'share_group_hash',
                    '_user_email',
                    '_file_name',
                    'collaboration_created',
                ], 'safe'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'Id',
            'file_parent_id' => 'Parent Id',
            'file_uuid' => 'uuid',
            'file_name' => 'Name',
            'file_size' => 'Size',
            'file_md5' => 'md5sum',
            'file_created_t' => 'Created',
            'file_lastatime' => 'Last access',
            'is_folder' => 'folder/file',
            'is_deleted' => 'deleted',
            'is_updated' => 'updated',
            'is_outdated' => 'outdated',
            'user_id' => 'users.user_id',
            'node_id' => 'user_node.node_id',
            'collaboration_id' => 'collaboration_id',
            'is_collaborated' => 'collaborated',
            'is_owner' => "owner",
            'is_shared' => 'shared',
            'share_hash' => 'Share hash',
            'share_group_hash' => 'Share group hash',
            'share_created' => 'Share created',
            'share_lifetime' => 'SHare lifetime',
            'share_ttl_info' => 'Share ttl',
            'share_password' => 'Share pass',
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
    public function searchShares($params)
    {
        $query = self::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email as _user_email")
            ->leftJoin('{{%users}} as t2', 't2.user_id = t1.user_id')
            ->where(['is_shared' => UserFiles::FILE_SHARED]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['share_created'=>SORT_DESC],
                'attributes' => [
                    'file_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                    'file_name',
                    'share_created',
                    'is_folder',
                    'file_size',
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
            'file_id'    => $this->file_id,
            'user_id'    => $this->user_id,
            'file_name'  => $this->file_name,
            //'share_hash' => $this->share_hash,
            'file_uuid'  => $this->file_uuid,
            'is_folder'  => $this->is_folder,
        ]);

        //var_dump($this->share_hash);
        if ($this->share_hash) {
            preg_match("/[a-z0-9]{32}/", $this->share_hash, $ma);
            //var_dump($ma);
            if (isset($ma[0])) {
                $query->andWhere("((share_hash = :share_hash) OR (share_group_hash = :share_hash))", [
                    'share_hash' => $ma[0],
                ]);
            } else {
                $query->andFilterWhere(['share_hash' => $this->share_hash]);
            }
        }

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->share_created) && strpos($this->share_created, '-') !== false) {
            $tmp = explode(' - ', $this->share_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'share_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchCollaborations($params)
    {
        $query = UserCollaborations::find()
            ->alias("t1")
            ->select("t1.*, t2.user_email, t3.file_id, t3.file_name")
            ->leftJoin('{{%users}} as t2', 't2.user_id = t1.user_id')
            ->innerJoin('{{%user_files}} as t3', '(t3.file_uuid = t1.file_uuid) AND (t3.user_id = t1.user_id)');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['collaboration_created'=>SORT_DESC],
                'attributes' => [
                    'collaboration_id',
                    '_user_email' => [
                        'asc' => ['t2.user_email' => SORT_ASC],
                        'desc' => ['t2.user_email' => SORT_DESC],
                    ],
                    '_file_name' => [
                        'asc' => ['t3.file_name' => SORT_ASC],
                        'desc' => ['t3.file_name' => SORT_DESC],
                    ],
                    'collaboration_created',
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
            'collaboration_id' => $this->collaboration_id,
            'user_id'          => $this->user_id,
            'file_uuid'  => $this->file_uuid,
        ]);

        $query->andFilterWhere(['like', 't2.user_email', $this->_user_email]);
        $query->andFilterWhere(['like', 't3.file_name', $this->_file_name]);

        // do we have values? if so, add a filter to our query
        if(!empty($this->collaboration_created) && strpos($this->collaboration_created, '-') !== false) {
            $tmp = explode(' - ', $this->collaboration_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'collaboration_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        return $dataProvider;
    }
}
