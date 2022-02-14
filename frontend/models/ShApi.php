<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\Licenses;
use common\models\SelfHostUsers;
use common\models\ShuCheckLog;
use common\models\UserLicenses;

/**
 * ShuApi
 *
 * @property \common\models\SelfHostUsers $SelfHostUser
 * @property \yii\redis\Connection $redis
 *
 */
class ShApi extends Model
{
    const CHECK_PERIOD_TTL = 36 * 60 * 60;
    const INTEGRITY_CHECK_TTL = 120;

    const ERROR_INVALID_JSON = 'INVALID_JSON';
    const ERROR_WRONG_DATA = 'WRONG_DATA';
    const ERROR_USER_NOT_FOUND = 'USER_NOT_FOUND';
    const ERROR_USER_LOCKED = 'USER_LOCKED';
    const ERROR_SIGNATURE_INVALID = 'SIGNATURE_INVALID';
    const ERROR_DATABASE_INTEGRITY = 'ERROR_DATABASE_INTEGRITY';
    const ERROR_SELF_HOSTED_CLIENT_BLOCKED = 'SELF_HOSTED_CLIENT_BLOCKED';
    const ERROR_MEMCACHE_STORE = 'MEMCACHE_STORE';

    const RESULT_ERROR = 'error';
    const RESULT_SUCCESS = 'success';

    protected $redis;
    protected $SelfHostUser;

    public $node_hash, $user_hash;

    public $shu_email;
    public $license_count_used;
    public $license_count_available;
    public $signal_passphrase;
    public $shu_user_hash;
    public $errcode;
    public $result;

    public $dynamic_rules = null;

    /**************************** +++ GLOBAL +++ ***************************/
    /**
     * ShApi constructor.
     * @param array $required_fields Поля которые будут проверяться на наличие в джсон
     */
    public function __construct(array $required_fields = []/*, UserFiles $CollaboratedFolder*/)
    {
        if (is_array($required_fields) && sizeof($required_fields)) {
            $this->dynamic_rules = [[$required_fields, 'required', 'message' => 'Fields ' . implode(', ', $required_fields) . ' are required.']];
        }
        $this->redis = Yii::$app->redis;

        parent::__construct();
    }

    /**
     * Правила валидации данных
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['signal_passphrase'], 'string', 'length' => 128],
            [['shu_email'], 'email'],
            [['license_count_used'], 'integer', 'min' => 1, 'max' => 999],
            [['license_count_available'], 'integer', 'min' => 0, 'max' => 999],
            [['errcode'], 'string'],
            [['result'], 'string'],
            [['result'], 'in', 'range' => [self::RESULT_ERROR, self::RESULT_SUCCESS]],
//            [['shu_email'],
//                'exist',
//                'skipOnError' => false,
//                'targetClass' => SelfHostUsers::className(),
//                'targetAttribute' => ['shu_email' => 'shu_email'],
//                'message' => 'User not found',
//            ],
        ];
        if (is_array($this->dynamic_rules)) {
            return array_merge($this->dynamic_rules, $rules);
        } else {
            return $rules;
        }
    }
    /**************************** --- GLOBAL --- ***************************/

//    public function afterValidate()
//    {
//        parent::afterValidate();
//        if (!$this->hasErrors()) {
//            $this->SelfHostUser = SelfHostUsers::findByEmail($this->shu_email);
//        }
//    }

    /**
     * Check integrity BD content for Self-Hosted
     * @return array
     */
    public static function check_sh_system_integrity()
    {
        /* если это сх, то нужно проверить в БД наличие юзеров с лицензией отличной от бизнес-админ и бизнес-юзер, если есть - ошибка*/
        unset($checkUsers);
        $checkUsers = Users::findAll(['license_type' => [
            Licenses::TYPE_FREE_TRIAL,
            Licenses::TYPE_PAYED_PROFESSIONAL,
        ]]);
        if (sizeof($checkUsers) > 0) {
            Yii::$app->session->set('system-fault-error', 'contains-prohibited-types-license');
            Yii::$app->cache->delete('last_license_check');
            return [
                'status' => false,
                'info'   => "contains-prohibited-types-license",
            ];
        }

        /* если это сх, то нужно проверить в БД количество бизнес-админов если больше одного то выдать ошибку системы (это хак) */
        unset($checkUsers);
        $checkUsers = Users::findAll(['license_type' => Licenses::TYPE_PAYED_BUSINESS_ADMIN]);
        if (sizeof($checkUsers) > 1) {
            Yii::$app->session->set('system-fault-error', 'more-then-one-business-admin-for-sh');
            Yii::$app->cache->delete('last_license_check');
            return [
                'status' => false,
                'info'   => "more-then-one-business-admin-for-sh"
            ];
        } elseif ($checkUsers === null || sizeof($checkUsers) == 0) {
            Yii::$app->session->set('system-fault-error', 'no-business-admin-user-in-db-for-sh');
            Yii::$app->cache->delete('last_license_check');
            return [
                'status' => false,
                'info'   => "no-business-admin-user-in-db-for-sh",
            ];
        }

        /**/
        Yii::$app->cache->set('sh_integrity_passed', true, self::INTEGRITY_CHECK_TTL);
        return [
            'status' => true,
            'user'   => $checkUsers[0],
            'info'   => "sh_integrity_passed",
        ];
    }

    /**
     * Метод license_check_data запрос приходит от сигнального на сервер https://domain (СХ сайт юзера)
     * @return array
     */
    public function license_check_data()
    {
        /**/
        $test = self::check_sh_system_integrity();
        if (!$test['status']) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_DATABASE_INTEGRITY,
                'info'    => $test['info'],
            ];
        }

        /**/
        $shu_user_hash = $test['user']->license_key_for_sh;
        $license_count_used = 1;
        $license_count_used = UserLicenses::find()
            ->where("(lic_owner_user_id = :lic_owner_user_id) AND (lic_colleague_email IS NOT NULL)", [
                'lic_owner_user_id' => $test['user']->user_id
            ])->count();

        return [
            'result'  => "success",
            'data' => [
                'shu_user_hash'      => $shu_user_hash,
                'license_count_used' => $license_count_used,
            ],
        ];

    }

    /**
     * Метод license_check запрос приходит от сигнального на сервер https://pvtbox.net
     * @return array;
     */
    public function license_check()
    {
        /* если пользователь не найден или заблокирован */
        $SelfHostUser = SelfHostUsers::findByShuHash($this->shu_user_hash);
        if (!$SelfHostUser) {
            return [
                'result' => "error",
                'errcode' =>  self::ERROR_SELF_HOSTED_CLIENT_BLOCKED,
                'debug' => 'User not found',
            ];
        }

        /* записываем время и ип последней проверки для этого юзера */
        $SelfHostUser->shu_license_last_check_ip = Yii::$app->request->getUserIP();
        $SelfHostUser->shu_license_last_check = date(SQL_DATE_FORMAT);
        $SelfHostUser->license_count_used = $this->license_count_used;

        /* записываем время и ип последней проверки в лог-бд для этого юзера */
        ShuCheckLog::deleteAll('(shu_id = :shu_id) AND (check_created < :min_check_created)', [
            'shu_id' => $SelfHostUser->shu_id,
            'min_check_created' => date(SQL_DATE_FORMAT, time() - 10 * 24 * 60 * 60),
        ]);
        $ShuCheckLog = new ShuCheckLog();
        $ShuCheckLog->shu_id = $SelfHostUser->shu_id;
        $ShuCheckLog->check_ip = Yii::$app->request->getUserIP();
        $ShuCheckLog->check_data = serialize([
            'license_count_used' => $SelfHostUser->license_count_used,
            'license_count_available' => $SelfHostUser->license_count_available,
            'check_ip' => $ShuCheckLog->check_ip,
            'check_created' => $ShuCheckLog->check_created,
        ]);
        $ShuCheckLog->save();

        /* если пользователь заблокирован */
        if (in_array($SelfHostUser->shu_status, [SelfHostUsers::STATUS_SH_LOCKED, SelfHostUsers::STATUS_LOCKED])) {
            $SelfHostUser->save();
            return [
                'result' => "error",
                'errcode' =>  self::ERROR_SELF_HOSTED_CLIENT_BLOCKED,
                'debug' => 'User blocked',
            ];
        }

        /* если писпользует больше лицензий чем доступно, то блокируем и ошибку даем */
        if ($SelfHostUser->license_count_available < $this->license_count_used) {
            $SelfHostUser->license_mismatch = SelfHostUsers::YES;
            $SelfHostUser->shu_status = SelfHostUsers::STATUS_SH_LOCKED;
            $SelfHostUser->save();
            return [
                'result' => "error",
                'errcode' =>  self::ERROR_SELF_HOSTED_CLIENT_BLOCKED,
                'debug' => 'license_count_used more than license_count_available',
                'data' => [
                    'license_count_available' => $SelfHostUser->license_count_available,
                ]
            ];
        } else {
            $SelfHostUser->shu_status = SelfHostUsers::STATUS_CONFIRMED;
            $SelfHostUser->save();
        }

        /* отдаем если все в порядке */
        return [
            'result' => "success",
            'data' => [
                'license_count_available' => $SelfHostUser->license_count_available,
            ]
        ];
    }

    /*
    При получении запроса license_check_result, если result = success:
    записать в кэш текущее время как время последней успешной проверки
    изменить число доступных лицензий для business admin пользователя согласно параметру license_count_available

    При получении запроса license_check_result, если result = error и errcode = SELF_HOSTED_CLIENT_BLOCKED:
    очистить время последней успешной проверки, поставить 0

    При обработке каждого запроса проверять в кэше время последней успешной проверки
    если время последней успешной проверки отличается от текущего времени на 36 часов и более - блокировать работу:
    отвечать ошибкой на запросы по апи (кроме /api/self-hosted)
    показать заглушку вместо сайта
    */
    /**
     * Метод license_check_result запрос приходит от сигнального на сервер https://domain (СХ сайт юзера)
     * @return array
     */
    public function license_check_result()
    {
        /**/
        $test = self::check_sh_system_integrity();
        if (!$test['status']) {
            return [
                'result'  => "error",
                'errcode' => ShApi::ERROR_DATABASE_INTEGRITY,
                'info'    => $test['info'],
            ];
        }

        /**/
        if ($this->result == self::RESULT_SUCCESS) {
            // тут еще на основе $this->license_count_available нужно проверить
            // что количество лицензий в бд не больше этого параметра
            // если больше то нужно создать процедуру уменьшения(сложнее) или увеличения(проще)

            /* процедура увеличения */
            $count = UserLicenses::getLicenseCountInfoForUser($test['user']->user_id);
            if ($this->license_count_available > $count['total']) {

                $delta = $this->license_count_available - $count['total'];
                for ($i = 1; $i <= $delta; $i++) {
                    $lic = new UserLicenses();
                    $lic->lic_start = $test['user']->user_created;
                    $lic->lic_end = $test['user']->license_expire;
                    $lic->lic_period = $test['user']->license_period;
                    $lic->lic_owner_user_id = $test['user']->user_id;
                    $lic->lic_colleague_user_id = null;
                    $lic->lic_colleague_email = null;
                    $lic->lic_lastpay_timestamp = time();
                    $lic->lic_group_id = $lic->lic_lastpay_timestamp;
                    $lic->save();
                }
            }

            if (!Yii::$app->cache->set('last_license_check', time(), self::CHECK_PERIOD_TTL)) {
                return [
                    'result' => "error",
                    'errcode' => ShApi::ERROR_MEMCACHE_STORE,
                    'info' => 'Failed save into memcache db.',
                ];
            }
            if (!Yii::$app->cache->get('last_license_check')) {
                return [
                    'result' => "error",
                    'errcode' => ShApi::ERROR_MEMCACHE_STORE,
                    'info' => 'Failed get value from memcache right away after save.',
                ];
            }
        } else {
            Yii::$app->cache->delete('last_license_check');
        }

        return [
            'result'  => "success",
        ];
    }

}
