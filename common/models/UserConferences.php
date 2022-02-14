<?php

namespace common\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%conferences}}".
 *
 * @property int $conference_id
 * @property string $conference_created
 * @property string $conference_updated
 * @property string $room_uuid
 * @property string $conference_name
 * @property string|null $conference_participants
 * @property int $conference_status
 * @property int $user_id
 * @property string $conference_guest_hash
 *
 * @property string $conference_guest_link
 *
 * @property Users $user
 */
class UserConferences extends ActiveRecord
{
    public $conference_guest_link;
    private static $CACHE_TTL = 3600;

    const STATUS_IDLE = 0;
    const STATUS_LIVE = 1;

    const VIEW_SINGLE  = 'single';
    const VIEW_GALLERY = 'gallery';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'conference_created',
                'updatedAtAttribute' => 'conference_updated',
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_conferences}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['conference_name', 'user_id'], 'required'],
            [['conference_created', 'conference_updated'], 'validateDateField', 'skipOnEmpty' => true],
            [['conference_created', 'conference_updated'], 'safe'],
            [['conference_participants'], 'string'],
            [['room_uuid', 'conference_status', 'user_id'], 'default', 'value' => null],
            [['conference_status', 'user_id', 'conference_id'], 'integer'],
            [['conference_status'], 'in', 'range' => [self::STATUS_IDLE, self::STATUS_LIVE]],
            [['room_uuid'], 'string', 'length' => 32],
            [['conference_guest_hash'], 'string', 'length' => 32],
            [['conference_name'], 'string', 'max' => 50],

            /* unique keys */
            [['conference_name', 'user_id'], 'unique', 'targetAttribute' => ['conference_name', 'user_id']],
            [['room_uuid'], 'unique', 'skipOnEmpty' => true],
            [['conference_guest_hash'], 'unique', 'skipOnEmpty' => true],

            /* foreign keys */
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateDateField($attribute, $params)
    {
        $check = Functions::checkDateIsValidForDB($this->$attribute);
        if (!$check) {
            $this->addError($attribute, 'Invalid date format');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'conference_id' => 'Conference ID',
            'conference_created' => 'Conference Created',
            'conference_updated' => 'Conference Updated',
            'room_uuid' => 'Room Unique Id',
            'conference_name' => 'Conference Name',
            'conference_participants' => 'Conference Participants',
            'conference_status' => 'Conference Status',
            'user_id' => 'User ID',
            'conference_guest_hash' => 'Conference Guest Hash',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @param int|string $id
     * @return UserConferences | null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['conference_id' => $id]);
        /*
        return self::getDb()->cache(
            function($db) use($id) {
                return static::findOne(['conference_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserConferences.conference_id.' . $id])
        );
        */
    }

    public function generateGuestHash()
    {
        $this->conference_guest_hash = md5(uniqid());
    }

    /**
     * @param string $room_uuid
     * @return UserConferences | null
     */
    public static function findByRoom($room_uuid)
    {
        return static::findOne(['room_uuid' => $room_uuid]);
    }

    /**
     * @param string $conference_guest_hash
     * @return UserConferences | null
     */
    public static function findByGuestHash($conference_guest_hash)
    {
        return static::findOne(['conference_guest_hash' => $conference_guest_hash]);
    }

    /**
     * @return string
     */
    public function getConferenceGuestLink()
    {
        return static::getConferenceGuestLinkBy($this->conference_guest_hash);
//        return Yii::$app->urlManager->createAbsoluteUrl([
//            'conferences/open-conference',
//            'conference_id' => $this->conference_id,
//            'guest' => $this->conference_guest_hash,
//        ]);
    }

    public static function getConferenceGuestLinkBy($conference_guest_hash)
    {
        return Yii::$app->urlManager->createAbsoluteUrl([
            'conferences/open-conference',
            //'conference_id' => $this->conference_id,
            'hash' => $conference_guest_hash,
        ]);
    }

    /**
     * returns list of statuses in array
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_IDLE => Yii::t('models/user-conferences', 'Idle'),
            self::STATUS_LIVE => Yii::t('models/user-conferences', 'Live'),
        ];
    }

    /**
     * return status name by colleague_status value
     * @param integer $conference_status
     * @return string | null
     */
    public static function getStatus($conference_status)
    {
        $params = self::getStatuses();
        return isset($params[$conference_status]) ? $params[$conference_status] : null;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->conference_guest_link = $this->getConferenceGuestLink();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->conference_guest_link = $this->getConferenceGuestLink();
    }
}
