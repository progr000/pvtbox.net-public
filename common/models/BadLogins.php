<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%bad_logins}}".
 *
 * @property int $bl_id ID
 * @property string $bl_type type of block
 * @property string $bl_created Creation Date
 * @property string $bl_updated Update Date
 * @property string $bl_ip IP
 * @property int $bl_count_tries Count of tries bad login
 * @property int $bl_last_timestamp Last bad login try
 * @property int $bl_locked locked or not (1 or 0)
 * @property int $bl_lock_seconds Count seconds for lock
 */
class BadLogins extends ActiveRecord
{
    const TYPE_LOCK_LOGIN = 'login';
    const TYPE_LOCK_RESET = 'resetpassword';
    const TYPE_LOCK_SHARE = 'share';
    const TYPE_LOCK_VALIDATE = 'validate';

    const IP_LOCKED = 1;
    const IP_UNLOCKED = 0;

    const MIN_TRY_LOGIN = 5;
    const MAX_TRY_LOGIN = 999;
    const MAX_LOCK_LOGIN = 86400;

    const MIN_TRY_SHARE = 5;
    const MAX_TRY_SHARE = 999;
    const MAX_LOCK_SHARE = 86400;

    const MIN_TRY_RESET = 1;
    const MAX_TRY_RESET = 999;
    const MAX_LOCK_RESET = 86400;

    const MIN_TRY_VALIDATE = 5;
    const MAX_TRY_VALIDATE = 999;
    const MAX_LOCK_VALIDATE = 86400;

    protected static $PERIODS_OF_LOCK_LOGIN = [
        //bad_login_count_tries => lock_time_seconds
        ['lock_seconds' => 5,                    'min_try' => self::MIN_TRY_LOGIN, 'max_try' => 9],
        ['lock_seconds' => 300,                  'min_try' => 10,                  'max_try' => 19],
        ['lock_seconds' => 1800,                 'min_try' => 20,                  'max_try' => 29],
        ['lock_seconds' => 3600,                 'min_try' => 30,                  'max_try' => 39],
        ['lock_seconds' => self::MAX_LOCK_LOGIN, 'min_try' => 40,                  'max_try' => self::MAX_TRY_LOGIN],
    ];

    protected static $PERIODS_OF_LOCK_SHARE = [
        //bad_login_count_tries => lock_time_seconds
        ['lock_seconds' => 5,                    'min_try' => self::MIN_TRY_SHARE, 'max_try' => 9],
        ['lock_seconds' => 300,                  'min_try' => 10,                  'max_try' => 19],
        ['lock_seconds' => 1800,                 'min_try' => 20,                  'max_try' => 29],
        ['lock_seconds' => 3600,                 'min_try' => 30,                  'max_try' => 39],
        ['lock_seconds' => self::MAX_LOCK_SHARE, 'min_try' => 40,                  'max_try' => self::MAX_TRY_SHARE],
    ];

    protected static $PERIODS_OF_LOCK_RESET = [
        //bad_login_count_tries => lock_time_seconds
        ['lock_seconds' => 60,                   'min_try' => self::MIN_TRY_RESET, 'max_try' => 4],
        ['lock_seconds' => 3600,                 'min_try' => 5,                   'max_try' => 9],
        ['lock_seconds' => self::MAX_LOCK_RESET, 'min_try' => 10,                  'max_try' => self::MAX_TRY_RESET],
    ];

    protected static $PERIODS_OF_LOCK_VALIDATE = [
        //bad_login_count_tries => lock_time_seconds
        ['lock_seconds' => 5,                       'min_try' => self::MIN_TRY_VALIDATE, 'max_try' => 9],
        ['lock_seconds' => 300,                     'min_try' => 10,                     'max_try' => 19],
        ['lock_seconds' => 1800,                    'min_try' => 20,                     'max_try' => 29],
        ['lock_seconds' => 3600,                    'min_try' => 30,                     'max_try' => 39],
        ['lock_seconds' => self::MAX_LOCK_VALIDATE, 'min_try' => 40,                     'max_try' => self::MAX_TRY_VALIDATE],
    ];

    /**
     *
     */
    public static function typesList()
    {
        return [
            self::TYPE_LOCK_LOGIN    => self::TYPE_LOCK_LOGIN,
            self::TYPE_LOCK_RESET    => self::TYPE_LOCK_RESET,
            self::TYPE_LOCK_SHARE    => self::TYPE_LOCK_SHARE,
            self::TYPE_LOCK_VALIDATE => self::TYPE_LOCK_VALIDATE,
        ];
    }

    /**
     * @param int $try
     * @param string $bl_type
     * @return int
     */
    public static function getLockTimeSeconds($try, $bl_type)
    {
        if ($bl_type == self::TYPE_LOCK_RESET) {

            $MIN_TRY  = self::MIN_TRY_RESET;
            $MAX_TRY  = self::MAX_TRY_RESET;
            $MAX_LOCK = self::MAX_LOCK_RESET;
            $PERIODS  = self::$PERIODS_OF_LOCK_RESET;

        } elseif ($bl_type == self::TYPE_LOCK_SHARE) {

            $MIN_TRY = self::MIN_TRY_SHARE;
            $MAX_TRY  = self::MAX_TRY_SHARE;
            $MAX_LOCK = self::MAX_LOCK_SHARE;
            $PERIODS  = self::$PERIODS_OF_LOCK_SHARE;
        }
        else {

            $MIN_TRY  = self::MIN_TRY_LOGIN;
            $MAX_TRY  = self::MAX_TRY_LOGIN;
            $MAX_LOCK = self::MAX_LOCK_LOGIN;
            $PERIODS  = self::$PERIODS_OF_LOCK_LOGIN;

        }

        if ($try < $MIN_TRY) {
            return 0;
        }

        if ($try >= $MAX_TRY) {
            return $MAX_LOCK;
        }

        foreach ($PERIODS as $v) {
            if ($try >= $v['min_try'] && $try <= $v['max_try']) {
                return $v['lock_seconds'];
            }
        }
        return 0;
    }

    /**
     * @param string $ip
     * @param string $bl_type
     * @return array
     */
    public static function checkIsIpLocked($ip, $bl_type)
    {
        $bl = self::findOne(['bl_ip' => $ip, 'bl_type' => $bl_type]);
        if ($bl && $bl->bl_locked) {
            if (time() - $bl->bl_last_timestamp <= $bl->bl_lock_seconds) {
                return [
                    'status'            => true,
                    'data' => [
                        'bl_count_tries'       => $bl->bl_count_tries,
                        'bl_lock_seconds'      => $bl->bl_lock_seconds,
                        'bl_last_timestamp'    => $bl->bl_last_timestamp,
                        'bl_current_timestamp' => time(),
                    ],
                    'info'   => "IP is locked for {$bl->bl_lock_seconds} seconds after last try at " . date(SQL_DATE_FORMAT, $bl->bl_last_timestamp),
                ];
            }
        }

        return [
            'status' => false,
            'info'   => "IP isn't locked",
            'data' => [],
        ];
    }

    /**
     * @param string $ip
     * @param string $bl_type
     */
    public static function setDataForIP($ip, $bl_type)
    {
        if ($bl_type == self::TYPE_LOCK_RESET) {

            $MAX_TRY = self::MAX_TRY_RESET;

        } elseif ($bl_type == self::TYPE_LOCK_SHARE) {

            $MAX_TRY = self::MAX_TRY_SHARE;

        } elseif ($bl_type == self::TYPE_LOCK_VALIDATE) {

            $MAX_TRY = self::MAX_TRY_VALIDATE;

        }else {

            $MAX_TRY  = self::MAX_TRY_LOGIN;

        }

        $bl = self::findOne(['bl_ip' => $ip, 'bl_type' => $bl_type]);
        if (!$bl) {
            $bl = new BadLogins();
            $bl->bl_ip = $ip;
            $bl->bl_count_tries = 1;
            $bl->bl_type = $bl_type;
        } else {
            $bl->bl_count_tries++;
        }
        if ($bl->bl_count_tries > $MAX_TRY) { $bl->bl_count_tries = $MAX_TRY; }
        $bl->bl_last_timestamp = time();

        $lock_seconds = self::getLockTimeSeconds($bl->bl_count_tries, $bl_type);
        if ($lock_seconds) {
            $bl->bl_locked = self::IP_LOCKED;
            $bl->bl_lock_seconds = $lock_seconds;
        } else {
            $bl->bl_locked = self::IP_UNLOCKED;
            $bl->bl_lock_seconds = 0;
        }
        $bl->save();
        //var_dump($bl->getErrors()); exit;
    }

    /**
     * @param string $ip
     * @param string $bl_type
     */
    public static function removeIpFromList($ip, $bl_type)
    {
        self::deleteAll(['bl_ip' => $ip, 'bl_type' => $bl_type]);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bad_logins}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'bl_created',
                'updatedAtAttribute' => 'bl_updated',
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bl_ip', 'bl_last_timestamp', 'bl_type'], 'required'],
            [['bl_type'], 'in', 'range' => [self::TYPE_LOCK_LOGIN, self::TYPE_LOCK_RESET, self::TYPE_LOCK_SHARE, self::TYPE_LOCK_VALIDATE]],
            [['bl_created', 'bl_updated'], 'validateDateField', 'skipOnEmpty' => true],
            [['bl_created', 'bl_updated'], 'safe'],
            [['bl_count_tries', 'bl_last_timestamp', 'bl_locked', 'bl_lock_seconds'], 'integer'],
            [['bl_count_tries'], 'default', 'value' => 1],
            [['bl_lock_seconds'], 'default', 'value' => 0],
            [['bl_locked'], 'default', 'value' => self::IP_UNLOCKED],
            [['bl_ip', 'bl_type'], 'string', 'max' => 32],
            [['bl_ip', 'bl_type'], 'unique', 'targetAttribute' => ['bl_ip', 'bl_type']],
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
            'bl_id' => 'ID',
            'bl_type' => 'type of block',
            'bl_created' => 'Creation Date',
            'bl_updated' => 'Update Date',
            'bl_ip' => 'IP',
            'bl_count_tries' => 'Count of tries bad login',
            'bl_last_timestamp' => 'Last bad login try',
            'bl_locked' => 'locked or not (1 or 0)',
            'bl_lock_seconds' => 'Count seconds for lock',
        ];
    }
}
