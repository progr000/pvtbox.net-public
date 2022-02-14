<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%licenses}}".
 *
 * @property integer $license_id
 * @property string $license_type
 * @property string $license_description
 * @property integer $license_limit_bytes
 * @property integer $license_limit_days
 * @property integer $license_limit_nodes
 * @property integer $license_count_available
 * @property integer $license_shares_count_in24
 * @property integer $license_max_shares_size
 * @property integer $license_max_count_children_on_copy
 * @property integer $license_block_server_nodes_above_bought
 *
 */
class Licenses extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    const TYPE_FREE_DEFAULT         = 'FREE_DEFAULT';
    const TYPE_FREE_TRIAL           = 'FREE_TRIAL';
    const TYPE_PAYED_PROFESSIONAL   = 'PAYED_PROFESSIONAL';
    const TYPE_PAYED_BUSINESS_ADMIN = 'PAYED_BUSINESS_ADMIN';
    const TYPE_PAYED_BUSINESS_USER  = 'PAYED_BUSINESS_USER';

    const PERIOD_NOT_SET  = 0;
    const PERIOD_DAILY    = 1;
    const PERIOD_MONTHLY  = 30;
    const PERIOD_ANNUALLY = 365;
    const PERIOD_ONETIME  = 32767; // unlimited one-time payed period  (32767 - потому что smallint в постгре не может быть больше)

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%licenses}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_type', 'license_description'], 'required'],
            [['license_type'], 'string', 'max' => 20],
            [['license_description'], 'string', 'max' => 255],
            [['license_type'], 'unique'],
            [[
                'license_limit_bytes',
                'license_limit_days',
                'license_limit_nodes',
                'license_count_available',
                'license_shares_count_in24',
                'license_max_shares_size',
                'license_max_count_children_on_copy',
                'license_block_server_nodes_above_bought',
            ], 'integer', 'min' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'license_id'                => 'Id',
            'license_type'              => 'License Type',
            'license_description'       => 'Description',
            'license_limit_bytes'       => 'The number of bytes allowed (If 0 then there is no limit).',
            'license_limit_days'        => 'The number of free days (If 0 then there is no limit).',
            'license_limit_nodes'       => 'The number of available nodes (If 0 then there is no limit).',
            'license_count_available'   => 'Number of licenses available.',
            'license_shares_count_in24' => 'The number of Shares per day (If 0 then there is no limit).',
            'license_max_shares_size'   => 'The maximum size of the file to be shared (bytes) (If 0 then there is no limit).',
            'license_max_count_children_on_copy' => 'The maximum allowed children in folder on it copy operation (If 0 then there is no limit).',
            'license_block_server_nodes_above_bought' => 'Lock or not logins (api) from server nodes if no more available license for it (1::lock, 0::not lock)',
        ];
    }

    /**
     * @param string $license_type
     * @return Licenses|null
     */
    public static function findByType($license_type)
    {
        return self::getDb()->cache(
            function($db) use($license_type) {
                return static::findOne(['license_type' => $license_type]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Licenses.license_type.' . $license_type])
        );

    }

    /**
     * returns list of types in array
     * @param bool $for_adm
     * @return array
     */
    public static function licenseTypes($for_adm=false)
    {
        return [
            self::TYPE_FREE_DEFAULT         => Yii::t('models/licenses', 'Free'),
            self::TYPE_FREE_TRIAL           => Yii::t('models/licenses', 'Free_Trial'),
            self::TYPE_PAYED_PROFESSIONAL   => Yii::t('models/licenses', 'Professional'),
            self::TYPE_PAYED_BUSINESS_ADMIN => Yii::t('models/licenses', 'Business' . ($for_adm ? ' (Admin)' : '')),
            self::TYPE_PAYED_BUSINESS_USER  => Yii::t('models/licenses', 'Business' . ($for_adm ? ' (User)' : '')),
        ];
    }

    /**
     * return type
     * @param integer $license_type
     * @param bool $for_adm
     * @return string | null
     */
    public static function getType($license_type, $for_adm=false)
    {
        $labels = self::licenseTypes($for_adm);
        return isset($labels[$license_type]) ? $labels[$license_type] : null;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['license_limit_bytes'])) {
            $Users = Users::findAll(['license_type' => $this->license_type]);
            if ($Users) {
                foreach ($Users as $User) {
                    $bytes_rest = $this->license_limit_bytes - $User->license_bytes_sent;
                    $User->license_bytes_allowed = ($bytes_rest > 0) ? $bytes_rest : 0;
                    $User->save();
                }
            }
        }
        $this->invalidateCache();
    }

    /**
     * Invalidate cache
     */
    protected function invalidateCache()
    {
        $tag_key = md5( 'license_cache' );
        TagDependency::invalidate(Yii::$app->cache, [
            'Licenses.license_type.' . $this->license_type,
            $tag_key,
        ]);
    }

    /**
     * @return int
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getCountDaysTrialLicense()
    {
        /** @var \common\models\Licenses $license */
        $license = Pages::getDb()->cache(
            function($db) {
                return self::findOne(['license_type' => self::TYPE_FREE_TRIAL]);
            },
            null,
            new TagDependency(['tags'  => md5( 'license_cache' )])
        );

        if (!$license) {
            return 0;
        }
        return $license->license_limit_days;
    }

    /**
     * @param bool $translate
     * @return array
     */
    public static function licensesBilledVars($translate=false)
    {
        if ($translate) {
            return [
                self::PERIOD_NOT_SET  => Yii::t('models/licenses', 'not_set'),
                self::PERIOD_ONETIME  => Yii::t('models/licenses', 'onetime'),
                self::PERIOD_DAILY    => Yii::t('models/licenses', 'daily_for_tests'),
                self::PERIOD_MONTHLY  => Yii::t('models/licenses', 'monthly'),
                self::PERIOD_ANNUALLY => Yii::t('models/licenses', 'annually'),
            ];
        } else {
            return [
                self::PERIOD_NOT_SET  => 'not_set',
                self::PERIOD_ONETIME  => 'onetime',
                self::PERIOD_DAILY    => 'daily_for_tests',
                self::PERIOD_MONTHLY  => 'monthly',
                self::PERIOD_ANNUALLY => 'annually',
            ];
        }
    }

    /**
     * @param $license_period
     * @param bool $translate
     * @return string
     */
    public static function getBilledByPeriod($license_period, $translate=false)
    {
        $labels = self::licensesBilledVars($translate);
        return isset($labels[$license_period]) ? $labels[$license_period] : $license_period;
    }

    /**
     * @param string $billed
     * @return int
     */
    public static function getPeriodByBilled($billed)
    {
        return (strtolower($billed) == self::getBilledByPeriod(Licenses::PERIOD_MONTHLY)) ? self::PERIOD_MONTHLY : self::PERIOD_ANNUALLY;
    }

    /**
     * @param string $date_check
     * @return bool
     */
    public static function checkIsExpireSoon($date_check)
    {
        $BonusPeriodLicense = Preferences::getValueByKey('BonusPeriodLicense', 72, 'integer') * 3600;
        $expire = strtotime($date_check);
        $now = time();

        return (($expire >= $now) && ($expire <= $now + $BonusPeriodLicense));
    }

    /**
     * @param string $date_check
     * @return bool
     */
    public static function checkIsExpired($date_check)
    {
        $expire = strtotime($date_check);
        $now = time();

        return ($expire <= $now);
    }

    /**
     * @param string $license_type
     * @param Users|null $User
     * @return int
     */
    public static function getCountLicenseLimitNodes($license_type, $User=null)
    {
        $license_limit_nodes = 0;
        $License = self::findByType($license_type);
        if ($License) {
            $license_limit_nodes = $License->license_limit_nodes;
        }

        if ($User && !($User->upl_limit_nodes === null)) {
            $license_limit_nodes = $User->upl_limit_nodes;
        }

        return $license_limit_nodes;
    }
}
