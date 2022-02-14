<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%servers}}".
 *
 * @property string $server_id
 * @property string $server_type
 * @property string $server_title
 * @property string $server_url
 * @property string $server_ip
 * @property integer $server_port
 * @property string $server_login
 * @property string $server_password
 * @property integer $server_status
 */
class Servers extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    const SERVER_TYPE_TURN  = "TURN";
    const SERVER_TYPE_STUN  = "STUN";
    const SERVER_TYPE_SIGN  = "SIGN";
    const SERVER_TYPE_PROXY = "PROXY";

    const SERVER_ACTIVE_YES = 1;
    const SERVER_ACTIVE_NO  = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%servers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['server_url', 'server_type', 'server_status', 'server_title'], 'required'],
            [['server_type'], 'in', 'range' => [self::SERVER_TYPE_STUN, self::SERVER_TYPE_TURN, self::SERVER_TYPE_SIGN, self::SERVER_TYPE_PROXY]],
            [['server_type'], 'default', 'value' => self::SERVER_TYPE_STUN],
            [['server_status'], 'in', 'range' => [self::SERVER_ACTIVE_YES, self::SERVER_ACTIVE_NO]],
            [['server_status'], 'default', 'value' => self::SERVER_ACTIVE_YES],
            [['server_port'], 'integer', 'min' => 0, 'max' => 65535],
            [['server_login', 'server_password'], 'string', 'max' => 50],
            //[['server_url'], 'string', 'min' => 3, 'max' => 255],
            //[['server_url'], 'match', 'pattern' => '/^(http|https|ssl|udp|ssh|socks)\:\/\/[a-z0-9]{1}[a-z0-9\/\.\-\_\+\=&#@?,;:]*$/i'],
            //[['server_url'], 'match', 'pattern' => '/^[a-z0-9]{1}[a-z0-9\.\-\_]*(\:[0-9]{1,5})?$/i'],
            ['server_url', 'string'],
            [['server_title'], 'string', 'max' => 255],
            [['server_ip'], 'string', 'max' => 15],
            //[['server_ip'], 'unique'],
            [['server_url'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'server_id' => 'Id',
            'server_type' => 'Type',
            'server_title' => 'Description',
            'server_url' => 'Connect URL',
            'server_ip' => 'Reserved Server IP',
            'server_port' => 'Reserved Server Port',
            'server_login' => 'Login',
            'server_password' => 'Password',
            'server_status' => 'Status',
        ];
    }

    /**
     * returns list of types in array
     *
     * @return array
     */
    public static function serverTypes()
    {
        return [
            self::SERVER_TYPE_STUN  => 'Stun',
            self::SERVER_TYPE_TURN  => 'Turn',
            self::SERVER_TYPE_SIGN  => 'Signal',
            self::SERVER_TYPE_PROXY => 'ProxyNode',
        ];
    }

    /**
     * return type
     * @param integer $server_type
     *
     * @return string | null
     */
    public static function getType($server_type)
    {
        $labels = self::serverTypes();
        return isset($labels[$server_type]) ? $labels[$server_type] : null;
    }

    /**
     * returns list of actives in array
     *
     * @return array
     */
    public static function serverStatus()
    {
        return [
            self::SERVER_ACTIVE_YES => 'Active',
            self::SERVER_ACTIVE_NO  => 'Deactivated',
        ];
    }

    /**
     * return status
     * @param integer $server_status
     *
     * @return string | null
     */
    public static function getStatus($server_status)
    {
        $labels = self::serverStatus();
        return isset($labels[$server_status]) ? $labels[$server_status] : null;
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getSignal()
    {
        return self::getDb()->cache(
            function($db) {
                return self::find()
                    ->where([
                        'server_type' => Servers::SERVER_TYPE_SIGN,
                        'server_status' => Servers::SERVER_ACTIVE_YES
                    ])
                    ->limit(1)
                    ->all();
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Servers.server_type.' . Servers::SERVER_TYPE_SIGN . '.server_status.' . Servers::SERVER_ACTIVE_YES])
        );
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getProxy()
    {
        return self::getDb()->cache(
            function($db) {
                return self::find()
                    ->where([
                        'server_type' => Servers::SERVER_TYPE_PROXY,
                        'server_status' => Servers::SERVER_ACTIVE_YES
                    ])
                    ->limit(1)
                    ->all();
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Servers.server_type.' . Servers::SERVER_TYPE_PROXY . '.server_status.' . Servers::SERVER_ACTIVE_YES])
        );
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->server_type == Servers::SERVER_TYPE_SIGN) {
            TagDependency::invalidate(Yii::$app->cache, [
                'Servers.server_type.' . Servers::SERVER_TYPE_SIGN . '.server_status.' . Servers::SERVER_ACTIVE_YES
            ]);
        }
    }

}
