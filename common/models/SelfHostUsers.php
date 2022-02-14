<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\caching\TagDependency;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%self_host_users}}".
 *
 * @property string $shu_id
 * @property string $shu_company
 * @property string $shu_name
 * @property string $shu_email
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $shu_created
 * @property string $shu_updated
 * @property integer $shu_status
 * @property integer $shu_role
 * @property integer $shu_support_status
 * @property number $shu_support_cost
 * @property integer $shu_brand_status
 * @property number $shu_brand_cost
 * @property integer $user_id
 * @property integer $static_timezone
 * @property integer $dynamic_timezone
 * @property string $pay_type
 * @property integer $license_period
 * @property string $license_expire
 * @property integer $shu_support_requested
 * @property integer $shu_brand_requested
 * @property string $shu_user_hash
 * @property integer $shu_business_status
 * @property integer $license_count_available
 * @property integer $license_mismatch
 * @property integer $license_count_used
 * @property string $shu_license_last_check
 * @property string $shu_license_last_check_ip
 * @property string $shu_promo_code
 *
 * @property string $_color
 * @property string $_sname
 */
class SelfHostUsers extends ActiveRecord implements IdentityInterface
{
    private static $CACHE_TTL = 3600;

    const MAX_AVAILABLE_LICENSES = 999;

    const ENABLED  = 1;
    const DISABLED = 0;

    const STATUS_LOCKED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_SH_LOCKED = 3;

    const TYPE_BUSINESS    = 1;
    const TYPE_OPEN_SOURCE = 0;

    const ROLE_ROOT = 0;

    const YES = 1;
    const NO  = 0;

    public $_color = 'M';
    public $_sname = '';

    /**
     *
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_LOCKED    => 'User locked',
            self::STATUS_ACTIVE    => 'Email sent',
            self::STATUS_CONFIRMED => 'Server working',
            self::STATUS_SH_LOCKED => 'Server blocked',
        ];
    }

    /**
     * @param $shu_status
     * @return mixed
     */
    public static function getStatus($shu_status)
    {
        return self::getStatuses()[$shu_status];
    }

    /**
     * @return array
     */
    public static function getRoles()
    {
        return [
            self::ROLE_ROOT   => 'root',
        ];
    }

    /**
     * @param $shu_role
     * @return mixed
     */
    public static function getRole($shu_role)
    {
        return self::getRoles()[$shu_role];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%self_host_users}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'shu_created',
                'updatedAtAttribute' => 'shu_updated',
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
            [['shu_email', 'shu_name', 'shu_company'], 'required'],

            [['shu_created', 'shu_updated', 'license_expire', 'shu_license_last_check'], 'validateDateField', 'skipOnEmpty' => true],
            [['shu_created', 'shu_updated', 'license_expire', 'shu_license_last_check', 'shu_license_last_check_ip'], 'safe'],

            [['shu_email', 'shu_name', 'shu_company'], 'trim'],
            [['shu_name', 'shu_company'], 'string', 'max' => 100],
            [['shu_email'], 'email'],

            [['shu_user_hash'], 'string', 'max' => 128],

            [['shu_support_cost', 'shu_brand_cost'], 'number'],

            [['shu_support_status', 'shu_brand_status'], 'integer'],
            [['shu_support_status', 'shu_brand_status'], 'in', 'range' => [self::ENABLED, self::DISABLED]],

            [['shu_business_status'], 'integer'],
            [['shu_business_status'], 'in', 'range' => [self::TYPE_BUSINESS, self::TYPE_OPEN_SOURCE]],

            [['shu_status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['shu_status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_LOCKED, self::STATUS_CONFIRMED, self::STATUS_SH_LOCKED]],

            [['shu_role'], 'in', 'range' => [self::ROLE_ROOT]],

            [['static_timezone', 'dynamic_timezone'], 'integer',  'min' => -43200, 'max' => 46800],
            [['static_timezone', 'dynamic_timezone'], 'default', 'value' => self::NO],

            [['pay_type'], 'in', 'range' => [Users::PAY_NOTSET, Users::PAY_CARD, Users::PAY_CRYPTO]],
            [['pay_type'], 'default', 'value' => Users::PAY_NOTSET],

            [['license_period'], 'integer'],
            [['license_period'], 'in', 'range' => [
                Licenses::PERIOD_NOT_SET,
                Licenses::PERIOD_DAILY,
                Licenses::PERIOD_MONTHLY,
                Licenses::PERIOD_ANNUALLY,
                Licenses::PERIOD_ONETIME,
            ]],
            [['license_period'], 'default', 'value' => Licenses::PERIOD_NOT_SET],

            [['license_count_available'], 'integer', 'min' => 0, 'max' => self::MAX_AVAILABLE_LICENSES],
            [['license_count_used'], 'integer', 'min' => 0, 'max' => self::MAX_AVAILABLE_LICENSES],

            [['shu_support_requested', 'shu_brand_requested', 'license_mismatch'], 'integer', 'max' => self::YES, 'min' => self::NO],

            [['shu_promo_code'], 'string', 'max' => 30],

            [['user_id'], 'integer'],
            [['shu_email'], 'unique', 'targetClass' => static::className(), 'message' => 'User with this email already exists'],
            [['shu_user_hash'], 'unique', 'targetClass' => static::className(), 'message' => 'User with this shu_user_hash already exists'],
        ];
    }

    /**
     * @return array
     */
    public static function getBusinessStatuses()
    {
        return [
            self::TYPE_BUSINESS    => 'Yes',
            self::TYPE_OPEN_SOURCE => 'No',
        ];
    }

    /**
     * @param $shu_business_status
     * @return mixed
     */
    public static function getBusinessStatus($shu_business_status)
    {
        return self::getBusinessStatuses()[$shu_business_status];
    }

    /**
     * @return array
     */
    public static function getSupportStatuses()
    {
        return [
            self::ENABLED    => 'Yes',
            self::DISABLED => 'No',
        ];
    }

    /**
     * @param $shu_support_status
     * @return mixed
     */
    public static function getSupportStatus($shu_support_status)
    {
        return self::getBusinessStatuses()[$shu_support_status];
    }

    /**
     * @return array
     */
    public static function getBrandStatuses()
    {
        return [
            self::ENABLED    => 'Yes',
            self::DISABLED => 'No',
        ];
    }

    /**
     * @param $shu_brand_status
     * @return mixed
     */
    public static function getBrandStatus($shu_brand_status)
    {
        return self::getBusinessStatuses()[$shu_brand_status];
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
     * @param int|string $id
     * @return static
     * @throws \Exception
     * @throws \Throwable
     */
    public static function findIdentity($id)
    {
        return self::getDb()->cache(
            function($db) use($id) {
                return static::findOne(['shu_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'SelfHostUsers.shu_id.' . $id])
        );
        //return static::findOne(['shu_id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by shu_email
     *
     * @param string $shu_email
     * @return static
     */
    public static function findByEmail($shu_email)
    {
        return self::getDb()->cache(
            function($db) use($shu_email) {
                return static::findOne([
                    'shu_email' => $shu_email,
                    'shu_status' => [
                        self::STATUS_ACTIVE,
                        self::STATUS_CONFIRMED,
                        self::STATUS_SH_LOCKED,
                    ]
                ]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'SelfHostUsers.shu_email.' . $shu_email])
        );
        //sreturn static::findOne(['shu_email' => $shu_email, 'shu_status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by shu_user_hash
     *
     * @param string $shu_user_hash
     * @return static
     */
    public static function findByShuHash($shu_user_hash)
    {
        return self::getDb()->cache(
            function($db) use($shu_user_hash) {
                return static::findOne([
                    'shu_user_hash' => $shu_user_hash,
                    'shu_status' => [
                        self::STATUS_ACTIVE,
                        self::STATUS_CONFIRMED,
                        self::STATUS_SH_LOCKED,
                    ]
                ]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'SelfHostUsers.shu_user_hash.' . $shu_user_hash])
        );
        //sreturn static::findOne(['shu_email' => $shu_email, 'shu_status' => self::STATUS_ACTIVE]);
    }


    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @param bool $checkTokenValid
     * @return static
     */
    public static function findByPasswordResetToken($token, $checkTokenValid=true)
    {
        if ($checkTokenValid && !self::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne(['password_reset_token' => $token]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Preferences::getValueByKey('user.passwordResetTokenExpire');
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->shu_user_hash = uniqid('pvt-');
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isLoginTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = 3600;
        return $timestamp + $expire >= time();
    }

    /**
     * Set color of user icon
     */
    public function setUserColor()
    {
        $icon = Users::getUserIcon($this->shu_email);
        $this->_color = $icon['color'];
        $this->_sname = $icon['sname'];
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->setUserColor();
    }

    /**
     * Invalidate Cache
     */
    protected function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'SelfHostUsers.shu_id.' . $this->shu_id,
            'SelfHostUsers.shu_email.' . $this->shu_email,
            'SelfHostUsers.shu_user_hash.' . $this->shu_user_hash,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->shu_promo_code === '') {
            $this->shu_promo_code = null;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->invalidateCache();
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function markUserAsDeleted()
    {
        /* Установим статус юзера как блокед-делетед и переименуем емейл */
        $this->shu_status = self::STATUS_LOCKED;
        $this->shu_email  = 'deleted_' . date('Y-m-d_H.i.s') . '___' . $this->shu_email;
    }

    /**
     * @return bool
     */
    public function requestSupportOrBrand()
    {
        $to = Preferences::getValueByKey("supportEmail_LICENSES", Preferences::getValueByKey('adminEmail'));

        return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
            'from_name'      => $this->shu_name,
            'reply_to_email' => $this->shu_email,
            'reply_to_name'  => $this->shu_name,
            'subject'        => "Request support or branding from SHU-User (ID={$this->shu_id} email={$this->shu_email})",
            'body'           => "Request support or branding from SHU-User (ID={$this->shu_id} email={$this->shu_email})",
            'to_name'        => 'Support',
        ]);
    }
}
