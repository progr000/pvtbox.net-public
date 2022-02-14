<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%notifications}}".
 *
 * @property string $notif_id
 * @property integer $notif_isnew
 * @property string $notif_data
 * @property string $notif_date
 * @property string $notif_type
 * @property string $user_id
 *
 * @property Users $user
 */
class Notifications extends ActiveRecord
{
    const IS_NEW = 1;
    const IS_OLD = 0;

    const TYPE_CONFERENCE_INVITE                  = 'conference_invite'; // ок
    const TYPE_CONFERENCE_EXCLUDE                 = 'conference_exclude'; // ок
    const TYPE_CONFERENCE_LEAVE                   = 'conference_leave'; //ok
    const TYPE_CONFERENCE_ABOUT_JOIN_FOR_ADMIN    = 'conference_about_join_for_admin'; // ok
    const TYPE_COLLABORATION_INCLUDE              = 'collaboration_include'; // ok
    const TYPE_COLLABORATION_INVITE               = 'collaboration_invite'; // ok
    const TYPE_COLLABORATION_JOIN                 = 'collaboration_join'; //
    const TYPE_COLLABORATION_ABOUT_JOIN_FOR_ADMIN = 'collaboration_about_join_for_admin'; // ok
    const TYPE_COLLABORATION_EXCLUDE              = 'collaboration_exclude'; // ok
    const TYPE_COLLABORATION_SELF_EXCLUDE         = 'collaboration_self_exclude'; // ok
    const TYPE_FOR_OWNER_COLLEAGUE_SELF_EXCLUDE   = 'for_owner_colleague_self_exclude'; // ok
    const TYPE_COLLABORATION_CHANGE_ACCESS        = 'collaboration_change_access'; // ok
    const TYPE_COLLABORATION_ADDED_FILES          = 'collaboration_added_files';
    const TYPE_COLLABORATION_DELETED_FILES        = 'collaboration_deleted_files';
    const TYPE_COLLABORATION_MOVED_FILES          = 'collaboration_moved_files';
    const TYPE_LICENSE_EXPIRED                    = 'license_expired'; // ok
    const TYPE_LICENSE_DOWNGRADED                 = 'license_downgraded'; // ok
    const TYPE_LICENSE_UPGRADED                   = 'license_upgraded'; // ok
    const TYPE_LICENSE_CHANGED                    = 'license_changed'; // ok
    const TYPE_BUSINESS_ADMIN_REMOVE_YOUR_LICENSE = 'business_admin_remove_your_license'; // ok

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notifications}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'notif_date',
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
            [['notif_isnew', 'user_id'], 'integer'],
            [['notif_date'], 'safe'],
            [['notif_data'], 'safe'],
            [['notif_type'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'notif_id' => 'ID',
            'notif_isnew' => 'New or Read',
            'notif_data' => 'Notif data',
            'notif_date' => 'Notif date',
            'notif_type' => 'Notif type',
            'user_id' => 'UserID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @param $user_id
     * @param array|null $ids
     * @return array
     */
    public static function seatAllAsRead($user_id, array $ids=null)
    {
        if ($ids && sizeof($ids)) {
            $where = [
                'user_id'  => $user_id,
                'notif_id' => $ids,
            ];
        } else {
            $where = ['user_id' => $user_id];
        }
        $countUpdated = self::updateAll(
            ['notif_isnew' => self::IS_OLD],
            $where
        );


        if ($ids && sizeof($ids)) {
            $countNew = self::find()->where([
                'user_id' => $user_id,
                'notif_isnew' => self::IS_NEW,
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
            'user_id'     => $this->user_id,
            'notif_isnew' => self::IS_NEW,
        ])->count();

        self::countToRedis($this->user_id, $count);
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
            $redis->publish("user:{$user_id}:new_notifications_count", $count);
            $redis->save();
        } catch (Exception $e) {
            RedisSafe::createNewRecord(
                RedisSafe::TYPE_NOTIFICATIONS_COUNT,
                $user_id,
                null,
                Json::encode([
                    'action'           => RedisSafe::TYPE_NOTIFICATIONS_COUNT,
                    'chanel'           => "user:{$user_id}:new_notifications_count",
                    'user_id'          => $count,
                ])
            );
        }
    }
}
