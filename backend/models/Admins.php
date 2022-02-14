<?php

namespace backend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\models\Preferences;
use common\models\Users;

/**
 * This is the model class for table "{{%admins}}".
 *
 * @property string $admin_id
 * @property string $admin_name
 * @property string $admin_email
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $admin_created
 * @property string $admin_updated
 * @property string $admin_auth_key
 * @property integer $admin_status
 * @property integer $admin_role
 */
class Admins extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE  = 1;

    const ROLE_ROOT   = 0;
    const ROLE_SELLER = 1;
    const ROLE_READER = 2;

    /**
     *
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DELETED => 'locked',
            self::STATUS_ACTIVE  => 'active',
        ];
    }

    /**
     * @param $admin_status
     * @return mixed
     */
    public static function getStatus($admin_status)
    {
        return self::getStatuses()[$admin_status];
    }

    /**
     * @return array
     */
    public static function getRoles()
    {
        return [
            self::ROLE_ROOT   => 'root',
            self::ROLE_SELLER => 'seller',
            self::ROLE_READER => 'reader',
        ];
    }

    /**
     * @param $admin_role
     * @return mixed
     */
    public static function getRole($admin_role)
    {
        return self::getRoles()[$admin_role];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admins}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'admin_created',
                'updatedAtAttribute' => 'admin_updated',
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
            [['admin_email', 'admin_name'], 'required'],
            [['admin_email', 'admin_name'], 'trim'],
            ['admin_email', 'email'],
            ['admin_email', 'unique', 'targetClass' => 'backend\models\Admins'],
            ['admin_status', 'default', 'value' => self::STATUS_ACTIVE],
            ['admin_status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['admin_role', 'in', 'range' => [self::ROLE_ROOT, self::ROLE_SELLER, self::ROLE_READER]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['admin_id' => $id, 'admin_status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by admin_name
     *
     * @param string $admin_name
     * @return static|null
     */
    public static function findByUsername($admin_name)
    {
        return static::findOne(['admin_name' => $admin_name, 'admin_status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by admin_email
     *
     * @param string $admin_email
     * @return static|null
     */
    public static function findByEmail($admin_email)
    {
        return static::findOne(['admin_email' => $admin_email, 'admin_status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @param bool $checkTokenValid
     * @return Admins|null
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

}
