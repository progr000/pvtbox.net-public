<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%user_licenses}}".
 *
 * @property int $lic_id Id
 * @property string $lic_start Дата начала лицензии
 * @property string $lic_end Дата завершения лицензии
 * @property int $lic_period Период действия лицензии
 * @property int $lic_owner_user_id Владелец лицензии
 * @property int $lic_colleague_user_id Ид юзера (коллеги) кому выдана лицензия
 * @property string $lic_colleague_email емейл юзера (коллеги) кому выдана лицензия
 * @property int $lic_lastpay_timestamp таймстамп последней оплаты по лицензии
 * @property int $lic_group_id групповой ид лицензии для удобства группировки лицензий в блоки (возможно лишнее поле)
 *
 * @property Users $licOwnerUser
 */
class UserLicenses extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_licenses}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lic_owner_user_id', 'lic_period'], 'required'], // added+++ 2019-03-08 11:00

            [['lic_start', 'lic_end'], 'validateDateField', 'skipOnEmpty' => true],
            [['lic_start', 'lic_end'], 'safe'],

            [['lic_period'], 'integer'],
            [['lic_period'], 'in', 'range' => [Licenses::PERIOD_DAILY, Licenses::PERIOD_MONTHLY, Licenses::PERIOD_ANNUALLY]],
            [['lic_period'], 'default', 'value' => Licenses::PERIOD_MONTHLY],

            [['lic_owner_user_id', 'lic_colleague_user_id', 'lic_lastpay_timestamp', 'lic_group_id'], 'integer'],

            [['lic_colleague_email'], 'email',], // added+++ 2019-03-08 11:00
            [['lic_colleague_email'], 'default', 'value' => null], // added+++ 2019-03-08 11:00

            /* defaults */
            [['lic_colleague_user_id'], 'default', 'value' => null],

            /* unique keys */
            [['lic_owner_user_id', 'lic_colleague_user_id'],
                'unique',
                'when' => function ($model) {
                    return !empty($model->lic_colleague_user_id);
                },
                'targetAttribute' => ['lic_owner_user_id', 'lic_colleague_user_id'],
                'message' => 'This colleague already exists in list.'
            ], // added+++ 2019-03-08 11:00
            [['lic_owner_user_id', 'lic_colleague_email'],
                'unique',
                'when' => function ($model) {
                    return !empty($model->lic_colleague_email);
                },
                'targetAttribute' => ['lic_owner_user_id', 'lic_colleague_email'],
                'message' => 'This colleague already exists in list.'
            ], // added+++ 2019-03-08 11:00

            /* foreign keys */
            [['lic_owner_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['lic_owner_user_id' => 'user_id'], 'message' => "User with this ID not exists in DB"],
            [['lic_colleague_user_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['lic_colleague_user_id' => 'user_id'], 'message' => "User with this ID not exists in DB"], // added+++ 2019-03-08 11:00
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
            'lic_id' => 'Id',
            'lic_start' => 'License date start',
            'lic_end' => 'License date finish',
            'lic_period' => 'License period',
            'lic_owner_user_id' => 'License owner',
            'lic_colleague_user_id' => 'UserID (colleague) who issued a license',
            'lic_colleague_email' => 'user_email (colleague) who issued a license',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicOwnerUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'lic_owner_user_id']);
    }

    /**
     * @param integer $owner_user_id
     * @return static
     */
    public static function getFreeLicense($owner_user_id)
    {
        return self::find()
            ->where("(lic_owner_user_id = :lic_owner_user_id)
                    AND (lic_colleague_user_id IS NULL)
                    AND (lic_colleague_email IS NULL)
                    AND (lic_end > :current_date)", [
                'lic_owner_user_id' => $owner_user_id,
                'current_date'      => date(SQL_DATE_FORMAT),
            ])
            ->orderBy(['lic_id' => SORT_ASC])
            ->one();
    }

    /**
     * @param integer $owner_user_id
     * @param string $colleague_email
     * @return static
     */
    public static function getFreeLicenseForNonRegistered($owner_user_id, $colleague_email)
    {
        $ret = self::find()
            ->where("(lic_owner_user_id = :lic_owner_user_id) AND (lic_colleague_email = :colleague_email)", [
                'lic_owner_user_id' => $owner_user_id,
                'colleague_email'   => $colleague_email,
            ])
            ->orderBy(['lic_id' => SORT_ASC])
            ->one();
        if (!$ret) {
            $ret = self::find()
                ->where("(lic_owner_user_id = :lic_owner_user_id)
                        AND (lic_colleague_user_id IS NULL)
                        AND (lic_colleague_email IS NULL) -- добавлено 06/11/2018 12:06
                        AND (lic_end > :current_date)", [
                    'lic_owner_user_id' => $owner_user_id,
                    'current_date'      => date(SQL_DATE_FORMAT),
                ])
                ->orderBy(['lic_id' => SORT_ASC])
                ->one();
        }
        return $ret;
    }

    /**
     * @param integer $owner_user_id
     * @param string|integer $colleague_user_id
     * @param string|null $colleague_email
     * @return static
     */
    public static function getLicenseUsedBy($owner_user_id, $colleague_user_id=null, $colleague_email=null)
    {
        $ret = null;
        if ($colleague_user_id) {
            $ret = self::findOne([
                'lic_owner_user_id' => $owner_user_id,
                'lic_colleague_user_id' => $colleague_user_id,
            ]);
        }
        if (!$ret && $colleague_email) {
            $ret = self::findOne([
                'lic_owner_user_id' => $owner_user_id,
                'lic_colleague_email' => $colleague_email,
            ]);
        }
        return $ret;
    }

    /**
     * @param integer $owner_user_id
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getLicenseCountInfoForUser($owner_user_id)
    {
        $query = "SELECT count(*) as total
                  FROM {{%user_licenses}}
                  WHERE lic_owner_user_id = :owner_user_id";
        $res = Yii::$app->db->createCommand($query, [
            'owner_user_id' => $owner_user_id,
        ])->queryOne();
        if (!isset($res['total'])) {
            $license['total'] = 0;
        } else {
            $license['total'] = $res['total'];
        }

        $query = "SELECT count(*) as unused
                  FROM {{%user_licenses}}
                  WHERE (lic_owner_user_id = :owner_user_id)
                  AND (lic_colleague_user_id IS NULL)
                  AND (lic_colleague_email IS NULL)";
        $res = Yii::$app->db->createCommand($query, [
            'owner_user_id' => $owner_user_id,
        ])->queryOne();
        if (!isset($res['unused'])) {
            $license['unused'] = 0;
        } else {
            $license['unused'] = $res['unused'];
        }

        $license['used'] = $license['total'] - $license['unused'];
        return $license;
    }


    /**
     * @param integer $user_id
     * @param bool $only_revoke
     */
    public static function revokeForUserId($user_id, $only_revoke=false)
    {
        /** @var \common\models\Users $user */
        /*
        $Users = Users::find()
            ->alias('t1')
            ->select("t1.*")
            ->innerJoin("{{%user_licenses}} as t2", "t1.user_id = t2.lic_owner_user_id")
            ->where("(t2.lic_owner_user_id = :user_id) AND (t2.lic_colleague_user_id != :user_id) AND (t1.license_type IN (:BUSINESS_USER, :FREE_DEFAULT))", [
                'user_id'       => $user_id,
                'BUSINESS_USER' => Licenses::TYPE_PAYED_BUSINESS_USER,
                'FREE_DEFAULT'  => Licenses::TYPE_FREE_DEFAULT,
            ])
            ->all();
        foreach ($Users as $user) {
            $user->license_type = Licenses::TYPE_FREE_DEFAULT;
            $user->previous_license_business_finish = null;
            $user->previous_license_business_from = null;
            $user->save();
        }
        */
        $query = "SELECT *
                  FROM {{%users}}
                  WHERE (user_id IN (
                      SELECT lic_colleague_user_id
                      FROM {{%user_licenses}}
                      WHERE (lic_owner_user_id = :this_user_id)
                      AND (lic_colleague_user_id IS NOT NULL)
                  )
                  AND (license_type = :TYPE_PAYED_BUSINESS_USER))
                  OR (license_business_from = :this_user_id)";

        //var_dump($query); exit;
        $res = Yii::$app->db->createCommand($query, [
            'this_user_id'             => $user_id,
            'TYPE_PAYED_BUSINESS_USER' => Licenses::TYPE_PAYED_BUSINESS_USER,
        ])->queryAll();

        foreach ($res as $v) {
            $User = Users::findIdentity($v['user_id']);
            if ($User) {
                $User->license_type = Licenses::TYPE_FREE_DEFAULT;
                $User->license_business_from = null;
                $User->previous_license_business_finish = null;
                $User->previous_license_business_from = null;
                $User->upl_limit_nodes = null;
                $User->upl_shares_count_in24 = null;
                $User->upl_max_shares_size = null;
                $User->upl_max_count_children_on_copy = null;
                $User->upl_block_server_nodes_above_bought = null;
                $User->save();
            }
            unset($User);
        }

        if (!$only_revoke) {
            self::deleteAll(['lic_owner_user_id' => $user_id]);
        } else {
            $query = "UPDATE {{%user_licenses}} SET
                        lic_colleague_user_id = null,
                        lic_colleague_email = null
                      WHERE (lic_owner_user_id = :lic_owner_user_id)
                      AND ((lic_colleague_user_id != :lic_owner_user_id) OR (lic_colleague_user_id IS NULL))";
            Yii::$app->db->createCommand($query, [
                'lic_owner_user_id' => $user_id,
            ])->execute();
        }
    }
}
