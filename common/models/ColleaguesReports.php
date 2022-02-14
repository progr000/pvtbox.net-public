<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%colleagues_reports}}".
 *
 * @property string $report_id
 * @property string $report_date
 * @property integer $report_timestamp
 * @property integer $report_isnew
 * @property string $collaboration_id
 * @property string $colleague_id
 * @property integer $file_id
 * @property integer $file_parent_id
 * @property integer $file_parent_id_before_event
 * @property string $file_name_after_event
 * @property string $file_name_before_event
 * @property string $parent_folder_name_after_event
 * @property string $parent_folder_name_before_event
 * @property integer $file_renamed
 * @property integer $file_moved
 * @property integer $is_rollback
 * @property integer $is_folder
 * @property string $colleague_user_email
 * @property integer $event_type
 * @property string $owner_user_id
 * @property string $colleague_user_id
 * @property string $colleague_node_id
 * @property integer $event_id
 *
 * @property integer $_report_date_ts
 *
 * @property Users $colleagueUser
 * @property UserFiles $file
 * @property Users $ownerUser
 *
 */
class ColleaguesReports extends ActiveRecord
{
    const IS_NEW = 1;
    const IS_OLD = 0;

    const IS_MOVED  = 1;
    const NOT_MOVED = 0;

    const IS_RENAMED  = 1;
    const NOT_RENAMED = 0;

    const TYPE_FOLDER = 1;
    const TYPE_FILE   = 0;

    const IS_ROLLBACK  = 1;
    const NOT_ROLLBACK = 0;

    const EXT_RPT_TYPE_RENAMED = 100;
    const EXT_RPT_TYPE_MOVE_AND_RENAMED = 101;
    const EXT_RPT_TYPE_RESTORE_PATCH = 102;
    const EXT_RPT_TYPE_COLLABORATION_CREATED = 103;
    const EXT_RPT_TYPE_COLLABORATION_DELETED = 104;

    public $_report_date_ts;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%colleagues_reports}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'report_date',
                'updatedAtAttribute' => null,
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['report_date', 'report_timestamp'], 'required'],
            [['report_date'], 'safe'],
            [[
                'report_timestamp',
                'collaboration_id',
                'colleague_id',
                'file_id',
                'file_parent_id',
                'file_parent_id_before_event',
                'event_type',
                'owner_user_id',
                'colleague_user_id',
                'colleague_node_id',
                'report_isnew',
                'file_renamed',
                'file_moved',
                'is_rollback',
                'is_folder',
                'event_id'], 'integer'],
            [[
                'file_name_after_event',
                'file_name_before_event',
                'parent_folder_name_after_event',
                'parent_folder_name_before_event'], 'string', 'max' => 255],
            [['colleague_user_email'], 'string', 'max' => 50],
            [['colleague_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['colleague_user_id' => 'user_id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserFiles::className(), 'targetAttribute' => ['file_id' => 'file_id']],
            [['owner_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['owner_user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'report_id' => 'ID',
            'report_date' => 'Report date',
            'report_timestamp' => 'Report timestamp',
            'report_isnew' => 'New or Read',
            'collaboration_id' => 'Collaboration Id',
            'colleague_id' => 'Colleague Id',
            'file_id' => 'file_id',
            'file_parent_id' => 'file_parent_id',
            'file_parent_id_before_event' => 'file_parent_id_before_event',
            'file_name_after_event' => 'file_name_after_event',
            'file_name_before_event' => 'file_name_before_event',
            'parent_folder_name_after_event' => 'parent_folder_name_after_event',
            'parent_folder_name_before_event' => 'parent_folder_name_before_event',
            'file_renamed' => 'A sign that the file is renamed',
            'file_moved' => 'A sign that the file is moved',
            'is_folder' => 'File or Folder',
            'event_type' => 'event_type',
            'owner_user_id' => 'owner_user_id',
            'colleague_user_id' => 'colleague_user_id',
            'colleague_user_email' => 'colleague_user_email',
            'colleague_node_id' => 'colleague_node_id',
            'event_id' => 'event_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->report_timestamp = time();
                $this->report_isnew = self::IS_NEW;
            } else {
                $this->report_isnew = self::IS_OLD;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_report_date_ts = strtotime($this->report_date);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getColleagueUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'colleague_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(UserFiles::className(), ['file_id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwnerUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'owner_user_id']);
    }

    /**
     * @param array $event_data
     * @param \common\models\UserColleagues $Colleague
     * @param \common\models\UserNode $UserNode
     * @return array
     */
    public static function createNewReport(array $event_data, $Colleague, $UserNode)
    {
        $User = Users::findIdentity($UserNode->user_id);
        //var_dump($event_data); exit;
        $Report = new ColleaguesReports();
        $Report->collaboration_id            = $Colleague->collaboration_id;
        $Report->colleague_id                = 0; //= $Colleague->colleague_id; //unknown можем найти по collaboration_id + colleague_user_id
        $Report->event_type                  = $event_data['data']['event_type_int'];
        $Report->event_id                    = $event_data['data']['event_id'];
        $Report->file_id                     = $event_data['data']['file_id'];
        $Report->file_parent_id              = $event_data['data']['file_parent_id'];
        $Report->file_parent_id_before_event = isset($event_data['data']['file_parent_id_before_event'])
            ? $event_data['data']['file_parent_id_before_event']
            : null;
        $Report->file_name_after_event  = isset($event_data['data']['file_name'])
            ? $event_data['data']['file_name']
            : '';
        $Report->file_name_before_event = isset($event_data['data']['file_name_before_event'])
            ? $event_data['data']['file_name_before_event']
            : '';
        $Report->parent_folder_name_after_event = isset($event_data['data']['parent_folder_name'])
            ? $event_data['data']['parent_folder_name']
            : '';
        $Report->parent_folder_name_before_event = isset($evednt_data['data']['parent_folder_name_before_event'])
            ? $event_data['data']['parent_folder_name_before_event']
            : '';
        $Report->file_moved = (isset($event_data['data']['file_moved']) && $event_data['data']['file_moved'])
            ? self::IS_MOVED
            : self::NOT_MOVED;
        $Report->file_renamed = (isset($event_data['data']['file_renamed']) && $event_data['data']['file_renamed'])
            ? self::IS_RENAMED
            : self::NOT_RENAMED;
        $Report->is_rollback = (isset($event_data['data']['is_restore_patch']) && $event_data['data']['is_restore_patch'])
            ? self::IS_ROLLBACK
            : self::NOT_ROLLBACK;
        $Report->is_folder = (isset($event_data['data']['is_folder']) && $event_data['data']['is_folder'])
            ? self::TYPE_FOLDER
            : self::TYPE_FILE;
        $Report->owner_user_id          = $Colleague->user_id;
        $Report->colleague_user_id      = $UserNode->user_id;
        $Report->colleague_user_email   = ($User) ? $User->user_email : '';
        $Report->colleague_node_id      = $UserNode->node_id;
        //var_dump($Report->save()); exit;
        if ($Report->save()) {
            return [
                'status' => true,
                'info' => 'ok',
            ];
        } else {
            return [
                'status' => false,
                'info' => $Report->getErrors(),
            ];
        }
    }

    /**
     * @param integer $user_id
     * @param array|null $ids
     * @return array
     */
    public static function seatAllAsRead($user_id, array $ids=null)
    {
        if ($ids && sizeof($ids)) {
            $where = [
                'owner_user_id'  => $user_id,
                'report_id' => $ids,
            ];
        } else {
            $where = ['owner_user_id' => $user_id];
        }
        $countUpdated = self::updateAll(
            ['report_isnew' => self::IS_OLD],
            $where
        );


        if ($ids && sizeof($ids)) {
            $countNew = self::find()->where([
                'owner_user_id' => $user_id,
                'report_isnew' => self::IS_NEW,
            ])->count();
        } else {
            $countNew = 0;
        }
        self::countToRedis($user_id, $countNew);

        return [
            'count_read'   => $countUpdated,
            'count_unread' => $countNew,
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $count = self::find()->where([
            'owner_user_id' => $this->owner_user_id,
            'report_isnew'   => self::IS_NEW,
        ])->count();

        self::countToRedis($this->owner_user_id, $count);
    }

    /**
     * @param $user_id
     * @param $count
     */
    public static function countToRedis($user_id, $count)
    {
        /** @var \yii\redis\Connection $redis */
        try {
            $redis = Yii::$app->redis;
            $redis->publish("user:{$user_id}:new_reports_count", $count);
            $redis->save();
        } catch (Exception $e) {
            RedisSafe::createNewRecord(
                RedisSafe::TYPE_REPORTS_COUNT,
                $user_id,
                null,
                Json::encode([
                    'action'           => RedisSafe::TYPE_REPORTS_COUNT,
                    'chanel'           => "user:{$user_id}:new_reports_count",
                    'user_id'          => $count,
                ])
            );
        }
    }
}
