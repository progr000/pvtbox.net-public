<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%user_colleagues}}".
 *
 * @property string $colleague_id
 * @property string $colleague_status
 * @property string $colleague_permission
 * @property string $colleague_invite_date
 * @property string $colleague_joined_date
 * @property string $colleague_email
 * @property string $user_id
 * @property string $collaboration_id
 *
 * @property UserCollaborations $collaboration
 * @property Users $user
 *
 *
 * @property integer $_colleague_invite_date_ts
 * @property integer $_colleague_joined_date_ts
 *
 */
class UserColleagues extends ActiveRecord
{
    public $_colleague_invite_date_ts;
    public $_colleague_joined_date_ts;

    const STATUS_JOINED      = 'joined';
    const STATUS_INVITED     = 'invited';
    //const STATUS_QUEUED      = 'queued';
    const STATUS_QUEUED_ADD  = 'queued_add';
    const STATUS_QUEUED_DEL  = 'queued_del';

    const PERMISSION_VIEW   = 'view';
    const PERMISSION_EDIT   = 'edit';
    const PERMISSION_DELETE = 'delete';
    const PERMISSION_OWNER  = 'owner';

    const REPEAT_INVITE_PERIOD_AFTER = 3 * 3600;

    /**
     * returns list of statuses in array
     * @return array
     */
    public static function statusParams()
    {
        return [
            self::STATUS_JOINED      => 'Joined',
            self::STATUS_INVITED     => 'Invited',
            //self::STATUS_QUEUED      => 'Queued',
            self::STATUS_QUEUED_ADD  => 'Queued for Add',
            self::STATUS_QUEUED_DEL  => 'Queued for Del',
        ];
    }

    /**
     * return status name by colleague_status value
     * @param string $colleague_status
     * @return string | null
     */
    public static function statusLabel($colleague_status)
    {
        $params = self::statusParams();
        return isset($params[$colleague_status]) ? $params[$colleague_status] : null;
    }

    /**
     * returns list of statuses in array
     * @return array
     */
    public static function permissionParams()
    {
        return [
            self::PERMISSION_EDIT   => Yii::t('models/user-colleagues', 'PERMISSION_EDIT'),
            self::PERMISSION_VIEW   => Yii::t('models/user-colleagues', 'PERMISSION_VIEW'),
            self::PERMISSION_OWNER  => Yii::t('models/user-colleagues', 'PERMISSION_OWNER'),
        ];
    }

    /**
     * return status name by colleague_status value
     * @param string $colleague_permission
     * @return string | null
     */
    public static function permissionLabel($colleague_permission)
    {
        $params = self::permissionParams();
        return isset($params[$colleague_permission]) ? $params[$colleague_permission] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_colleagues}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['colleague_email', 'collaboration_id'], 'required'],

            [['colleague_status'], 'string'],
            [['colleague_status'], 'in', 'range' => [
                self::STATUS_INVITED,
                self::STATUS_JOINED,
                self::STATUS_QUEUED_ADD,
                self::STATUS_QUEUED_DEL,
            ]], // added+++ 2019-03-07 13:50
            [['colleague_status'], 'default', 'value' => self::STATUS_INVITED],

            [['colleague_permission'], 'string'],
            [['colleague_permission'], 'in', 'range' => [
                self::PERMISSION_VIEW,
                self::PERMISSION_EDIT,
                self::PERMISSION_OWNER,
                self::PERMISSION_DELETE,
            ]], // added+++ 2019-03-07 13:50
            [['colleague_permission'], 'default', 'value' => self::PERMISSION_VIEW],

            [['colleague_invite_date', 'colleague_joined_date'], 'validateDateField', 'skipOnEmpty' => true],
            [['colleague_invite_date', 'colleague_joined_date'], 'safe'],

            [['user_id', 'collaboration_id'], 'integer'],

            [['colleague_email'], 'email'], // added+++ 2019-03-07 13:50

            /* unique keys */
            [['colleague_email', 'user_id', 'collaboration_id'], 'unique', 'targetAttribute' => ['colleague_email', 'user_id', 'collaboration_id'], 'message' => 'This colleague already exists in list.'], // added+++ 2019-03-07 13:50
            [['colleague_email', 'collaboration_id'], 'unique', 'targetAttribute' => ['colleague_email', 'collaboration_id'], 'message' => 'This colleague already exists in list.'],
            [['user_id', 'collaboration_id'],
                'unique',
                'when' => function ($model) {
                    return !empty($model->user_id);
                },
                'targetAttribute' => ['user_id', 'collaboration_id'],
                'message' => 'This colleague already exists in list.'
            ], // removed очень давно // changed+++ 2019-03-07 13:50

            /* foreign keys */
            [['collaboration_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserCollaborations::className(), 'targetAttribute' => ['collaboration_id' => 'collaboration_id'], 'message' => "Collaboration with this ID not exists in DB"],
            [['user_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id'], 'message' => "User with this ID not exists in DB"], // changed+++ 2019-03-07 13:50
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'colleague_id' => 'ID',
            'colleague_status' => 'Status joined|invited',
            'colleague_permission' => 'Permissions view|edit',
            'colleague_invite_date' => 'Invite date',
            'colleague_joined_date' => 'Join date',
            'colleague_email' => 'Colleague email',
            'user_id' => 'UserID. If NULL than mean user is not registered in bd',
            'collaboration_id' => 'Link to user_collaborations.collaboration_id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCollaboration()
    {
        return $this->hasOne(UserCollaborations::className(), ['collaboration_id' => 'collaboration_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $User = Users::findOne(['user_email' => $this->colleague_email]);
            if ($User) {
                $this->user_id = $User->user_id;
            }
            $this->colleague_invite_date = date(SQL_DATE_FORMAT);
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        if (isset(Yii::$app->session)) {
            $UserTimeZoneOffset = Yii::$app->session->get('UserTimeZoneOffset', 0);
        } else {
            $UserTimeZoneOffset = 0;
        }
        $this->_colleague_joined_date_ts = strtotime($this->colleague_joined_date) + $UserTimeZoneOffset;
        $this->_colleague_invite_date_ts = strtotime($this->colleague_invite_date) + $UserTimeZoneOffset;
    }

    /**
     * @param \common\models\UserColleagues $UserColleague
     * @return array
     */
    public static function prepareColleagueData($UserColleague)
    {
        $icon = Users::getUserIcon($UserColleague->colleague_email);
        if (isset(Yii::$app->session)) {
            $UserTimeZoneOffset = Yii::$app->session->get('UserTimeZoneOffset', 0);
        } else {
            $UserTimeZoneOffset = 0;
        }
        if (!$UserColleague->_colleague_joined_date_ts) {
            $UserColleague->_colleague_joined_date_ts = strtotime($UserColleague->colleague_joined_date) + $UserTimeZoneOffset;
        }
        if (!$UserColleague->_colleague_invite_date_ts) {
            $UserColleague->_colleague_invite_date_ts = strtotime($UserColleague->colleague_invite_date) + $UserTimeZoneOffset;
        }
        return [
            'color'  => $icon['color'],
            'name'   => $icon['sname'],
            'email'  => $UserColleague->colleague_email,
            'status' => self::statusLabel($UserColleague->colleague_status),
            'date_utc' => ($UserColleague->colleague_status == self::STATUS_JOINED)
                ? $UserColleague->colleague_joined_date
                : $UserColleague->colleague_invite_date,
            'date'   => ($UserColleague->colleague_status == self::STATUS_JOINED)
                ? date(Yii::$app->params['datetime_format'], $UserColleague->_colleague_joined_date_ts)
                : date(Yii::$app->params['datetime_format'], $UserColleague->_colleague_invite_date_ts),
            'ts'     =>  ($UserColleague->colleague_status == self::STATUS_JOINED)
                ? $UserColleague->_colleague_joined_date_ts
                : $UserColleague->_colleague_invite_date_ts,
            'access_type' => $UserColleague->colleague_permission,
            'access_type_name' => self::permissionLabel($UserColleague->colleague_permission),
            'colleague_id' => $UserColleague->colleague_id,
            'user_id' => $UserColleague->user_id,
        ];
    }

    /**
     * @param array $UserColleague
     * @return array
     */
    public static function prepareColleagueDataFromArray($UserColleague)
    {
        $icon = Users::getUserIcon($UserColleague['colleague_email']);
        if (isset(Yii::$app->session)) {
            $UserTimeZoneOffset = Yii::$app->session->get('UserTimeZoneOffset', 0);
        } else {
            $UserTimeZoneOffset = 0;
        }
        if (!isset($UserColleague['_colleague_joined_date_ts'])) {
            $UserColleague['_colleague_joined_date_ts'] = strtotime($UserColleague['colleague_joined_date']) + $UserTimeZoneOffset;
        }
        if (!isset($UserColleague['_colleague_invite_date_ts'])) {
            $UserColleague['_colleague_invite_date_ts'] = strtotime($UserColleague['colleague_invite_date']) + $UserTimeZoneOffset;
        }
        return [
            'color'  => $icon['color'],
            'name'   => $icon['sname'],
            'email'  => $UserColleague['colleague_email'],
            'status' => self::statusLabel($UserColleague['colleague_status']),
            'date_utc' => ($UserColleague['colleague_status'] == self::STATUS_JOINED)
                ? $UserColleague['colleague_joined_date']
                : $UserColleague['colleague_invite_date'],
            'date'   => ($UserColleague['colleague_status'] == self::STATUS_JOINED)
                ? date(Yii::$app->params['datetime_format'], $UserColleague['_colleague_joined_date_ts'])
                : date(Yii::$app->params['datetime_format'], $UserColleague['_colleague_invite_date_ts']),
            'ts'     =>  ($UserColleague['colleague_status'] == self::STATUS_JOINED)
                ? $UserColleague['_colleague_joined_date_ts']
                : $UserColleague['_colleague_invite_date_ts'],
            'access_type' => $UserColleague['colleague_permission'],
            'access_type_name' => self::permissionLabel($UserColleague['colleague_permission']),
            'colleague_id' => $UserColleague['colleague_id'],
            'user_id' => $UserColleague['user_id'],
        ];
    }
}
