<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%conference_participants}}".
 *
 * @property int $participant_id
 * @property int $participant_status
 * @property string|null $participant_invite_date
 * @property string|null $participant_joined_date
 * @property string|null $participant_last_activity
 * @property string $participant_email
 * @property int $conference_id
 * @property int|null $user_id
 *
 * @property UserConferences $conference
 * @property Users $user
 */
class ConferenceParticipants extends ActiveRecord
{

    const STATUS_INVITED = 0;
    const STATUS_JOINED  = 1;
    const STATUS_OWNER   = 2;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'participant_invite_date',
                'updatedAtAttribute' => null, //'participant_last_activity',
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%conference_participants}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['participant_email', 'conference_id'], 'required'],
            [['participant_status', 'conference_id', 'user_id'], 'default', 'value' => null],
            [['participant_status', 'conference_id', 'user_id'], 'integer'],
            [['participant_status'], 'in', 'range' => [self::STATUS_INVITED, self::STATUS_JOINED, self::STATUS_OWNER]],

            [['participant_invite_date', 'participant_joined_date', 'participant_last_activity'], 'validateDateField', 'skipOnEmpty' => true],
            [['participant_invite_date', 'participant_joined_date', 'participant_last_activity'], 'safe'],
            [['participant_email'], 'email'],

            /* unique keys */
            [['participant_email', 'conference_id'], 'unique', 'targetAttribute' => ['participant_email', 'conference_id']],
            [['participant_email', 'user_id', 'conference_id'], 'unique', 'targetAttribute' => ['participant_email', 'user_id', 'conference_id']],
            [['user_id', 'conference_id'],
                'unique',
                'when' => function ($model) {
                    return !empty($model->user_id);
                },
                'targetAttribute' => ['user_id', 'conference_id']
            ],

            /* foreign keys */
            [['conference_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserConferences::className(), 'targetAttribute' => ['conference_id' => 'conference_id']],
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
            'participant_id' => 'Participant ID',
            'participant_status' => 'Participant Status',
            'participant_invite_date' => 'Participant Invite Date',
            'participant_joined_date' => 'Participant Joined Date',
            'participant_last_activity' => 'Participant Last Activity',
            'participant_email' => 'Participant Email',
            'conference_id' => 'Conference ID',
            'user_id' => 'User ID',
        ];
    }

    /**
     * Gets query for [[Conference]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConference()
    {
        return $this->hasOne(UserConferences::className(), ['conference_id' => 'conference_id']);
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
     * returns list of statuses in array
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_JOINED  => Yii::t('models/conference-participants', 'Joined'),
            self::STATUS_INVITED => Yii::t('models/conference-participants', 'Invited'),
            self::STATUS_OWNER   => Yii::t('models/conference-participants', 'Owner'),
        ];
    }

    /**
     * return status name by colleague_status value
     * @param integer $participant_status
     * @return string | null
     */
    public static function getStatus($participant_status)
    {
        $params = self::getStatuses();
        return isset($params[$participant_status]) ? $params[$participant_status] : null;
    }

}
