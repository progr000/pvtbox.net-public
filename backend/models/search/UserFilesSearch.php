<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\UserFiles;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UserFilesSearch extends UserFiles
{
    public $tab;
    public $file_created_t;
    public $show_deleted;

    /**
     * @inheritdoc
     */
    public function rules()
    {
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
                    'file_created_t',
                    'show_deleted',
                ], 'integer'
            ],
            [
                [
                    'tab',
                    'file_uuid',
                    'file_name',
                    'file_created',
                    'file_updated',
                    'file_lastatime',
                    'share_hash',
                    'share_group_hash',
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
            'collaboration_id' => 'user_collaborations.collaboration_id',
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
     * @param integer $user_id
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($user_id, $params)
    {
        $query = self::find()
            ->select("*, extract(epoch from file_created) as file_created_t")
            ->where(['user_id' => $user_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => array_merge($_GET, ['tab' => 'file-info', '#' => 'file-info']),
                'sortParam' => 'sort-p2',
                'defaultOrder' => ['file_id'=>SORT_DESC],
                'attributes' => [
                    'file_id',
                    'file_name',
                    'file_size',
                    'file_created_t',
                    'file_created',
                    'file_updated',
                    'file_lastatime',
                    'is_folder',
                    'share_hash',
                    'is_deleted',
                    'collaboration_id',
                ]
            ],
            'pagination' => [
                'params' => array_merge($_GET, ['tab' => 'file-info', '#' => 'file-info']),
                'pageParam' => 'p-fl-inf',
                'pageSizeParam' => 'per-p-fl-inf',
                'pageSize' => 100,
                //'route' => \yii\helpers\Url::current(['tab' => 'node-info', 'id' => $user_id])
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            //'user_id'         => $this->user_id,
            'file_id'         => $this->file_id,
            'file_uuid'       => $this->file_uuid,
            //'file_created'    => $this->file_created,
            //'file_updated'    => $this->file_updated,
            'is_folder'       => $this->is_folder,
            'is_deleted'      => $this->is_deleted,
            'collaboration_id' => $this->collaboration_id,
            //'node_name'       => $this->node_name,
        ]);

        if ($this->share_hash) {
            preg_match("/[a-z0-9]{32}/", $this->share_hash, $ma);
            if (isset($ma[0])) {
                $query->andWhere("((share_hash = :share_hash) OR (share_group_hash = :share_hash))", [
                    'share_hash' => $ma[0],
                ]);
            } else {
                $query->andFilterWhere(['share_hash' => $this->share_hash]);
            }
        }

        if (!$this->show_deleted) {
            $query->andFilterWhere([
                'is_deleted' => UserFiles::FILE_UNDELETED,
            ]);
        }

        // do we have values? if so, add a filter to our query
        if(!empty($this->file_created) && strpos($this->file_created, '-') !== false) {
            $tmp = explode(' - ', $this->file_created);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'file_created',
                    date(SQL_DATE_FORMAT, Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date))),
                    date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date))),
                ]);
            }
        }

        // do we have values? if so, add a filter to our query
        if(!empty($this->file_lastatime) && strpos($this->file_lastatime, '-') !== false) {
            $tmp = explode(' - ', $this->file_lastatime);
            if (isset($tmp[0], $tmp[1])) {
                $start_date = $tmp[0];
                $end_date = $tmp[1];

                $query->andFilterWhere([
                    'between',
                    'file_lastatime',
                    Functions::getTimestampBeginOfDayByTimestamp(strtotime($start_date)),
                    Functions::getTimestampEndOfDayByTimestamp(strtotime($end_date)),
                ]);
            }
        }

        $query->andFilterWhere(['like', 'file_name', $this->file_name]);

        return $dataProvider;
    }

    /**
     * @param integer $user_id
     * @return array
     */
    public function getCountFoldersAndFiles($user_id)
    {
        $query = "SELECT
                    count(*) as cnt, is_folder, is_deleted
                  FROM {{%user_files}}
                  WHERE user_id = :user_id
                  GROUP BY is_folder, is_deleted
                  ORDER BY is_folder DESC, is_deleted DESC";
        $res = Yii::$app->db->createCommand($query, [
            'user_id' => $user_id,
        ])->queryAll();
        $ret = [
            'folders_total'     => 0,
            'folders_deleted'   => 0,
            'folders_undeleted' => 0,
            'files_total'       => 0,
            'files_deleted'     => 0,
            'files_undeleted'   => 0,
        ];
        if (is_array($res)) {
            foreach ($res as $v) {
                $v['is_folder']  = intval($v['is_folder']);
                $v['is_deleted'] = intval($v['is_deleted']);
                $v['cnt']        = intval($v['cnt']);
                if ($v['is_folder'] && $v['is_deleted'])  { $ret['folders_deleted']   = $v['cnt']; }
                if ($v['is_folder'] && !$v['is_deleted']) { $ret['folders_undeleted'] = $v['cnt']; }

                if (!$v['is_folder'] && $v['is_deleted'])  { $ret['files_deleted']    = $v['cnt']; }
                if (!$v['is_folder'] && !$v['is_deleted']) { $ret['files_undeleted']  = $v['cnt']; }
            }

            $ret['folders_total'] = $ret['folders_deleted'] + $ret['folders_undeleted'];
            $ret['files_total']   = $ret['files_deleted'] + $ret['files_undeleted'];
        }

        return $ret;
    }

    /**
     * @param $share_hash
     * @return \common\models\UserFiles
     */
    public function findFileByShareHash($share_hash)
    {
        return self::find()
            ->where(['share_hash' => $share_hash])
            ->orWhere(['share_group_hash' => $share_hash, 'is_shared' => self::FILE_SHARED])
            ->one();
    }
}
