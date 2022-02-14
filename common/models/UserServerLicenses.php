<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%user_server_licenses}}".
 *
 * @property int $lic_srv_id Id
 * @property string $lic_srv_start Дата начала лицензии
 * @property string $lic_srv_end Дата завершения лицензии
 * @property int $lic_srv_period Период действия лицензии
 * @property int $lic_srv_owner_user_id Владелец лицензии
 * @property int $lic_srv_colleague_user_id Ид юзера (коллеги) кому присвоена лицензия
 * @property string $lic_srv_node_id Ид ноды которой присвоена лицензия
 * @property int $lic_srv_lastpay_timestamp таймстамп последней оплаты по лицензии
 * @property int $lic_srv_group_id групповой ид лицензии для удобства группировки лицензий в блоки (возможно лишнее поле)
 *
 * @property Users $licOwnerUser
 */
class UserServerLicenses extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_server_licenses}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lic_srv_owner_user_id', 'lic_srv_period'], 'required'], // added+++ 2019-03-08 11:00

            [['lic_srv_start', 'lic_srv_end'], 'validateDateField', 'skipOnEmpty' => true],
            [['lic_srv_start', 'lic_srv_end'], 'safe'],

            [['lic_srv_period'], 'integer'],
            [['lic_srv_period'], 'in', 'range' => [Licenses::PERIOD_DAILY, Licenses::PERIOD_MONTHLY, Licenses::PERIOD_ANNUALLY]],
            [['lic_srv_period'], 'default', 'value' => Licenses::PERIOD_MONTHLY],

            [['lic_srv_owner_user_id', 'lic_srv_colleague_user_id', 'lic_srv_lastpay_timestamp', 'lic_srv_group_id'], 'integer'],

            /* defaults */
            [['lic_srv_colleague_user_id'], 'default', 'value' => null],
            [['lic_srv_node_id'], 'default', 'value' => null],

            /* unique keys */
            [['lic_srv_owner_user_id', 'lic_srv_node_id'],
                'unique',
                'when' => function ($model) {
                    return !empty($model->lic_srv_node_id);
                },
                'targetAttribute' => ['lic_srv_owner_user_id', 'lic_srv_node_id'],
                'message' => 'This combination lic_srv_owner_user_id+lic_srv_node_id already exists in list.'
            ], // added+++ 2019-03-08 11:00

            /* foreign keys */
            [['lic_srv_owner_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['lic_srv_owner_user_id' => 'user_id'], 'message' => "User with this ID not exists in DB"],
            [['lic_srv_colleague_user_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['lic_srv_colleague_user_id' => 'user_id'], 'message' => "User with this ID not exists in DB"],
            [['lic_srv_node_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['lic_srv_node_id' => 'node_id'], 'message' => "Node with this ID not exists in DB"],
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
            'lic_srv_id' => 'Id',
            'lic_srv_start' => 'License date start',
            'lic_srv_end' => 'License date finish',
            'lic_srv_period' => 'License period',
            'lic_srv_owner_user_id' => 'Server license owner',
            'lic_srv_colleague_user_id' => 'UserID (colleague) who issued a server license',
            'lic_srv_node_id' => 'NodeID (colleague) who issued a license',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicOwnerUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'lic_srv_owner_user_id']);
    }

    /**
     * @param integer $owner_user_id
     * @return static
     */
    public static function getFreeLicense($owner_user_id)
    {
        return self::find()
            ->where("(lic_srv_owner_user_id = :lic_srv_owner_user_id)
                    AND (lic_srv_colleague_user_id IS NULL)
                    AND (lic_srv_node_id IS NULL)
                    AND (lic_srv_end > :current_date)", [
                'lic_srv_owner_user_id' => $owner_user_id,
                'current_date'      => date(SQL_DATE_FORMAT),
            ])
            ->orderBy(['lic_srv_id' => SORT_ASC])
            ->one();
    }

    /**
     * @param integer $owner_user_id
     * @param integer $colleague_user_id
     * @return static
     */
    public static function getLicenseUsedByColleague($owner_user_id, $colleague_user_id)
    {
        return self::findAll([
            'lic_srv_owner_user_id' => $owner_user_id,
            'lic_srv_colleague_user_id' => $colleague_user_id,
        ]);
    }

    /**
     * @param integer $owner_user_id
     * @param integer $node_id
     * @return static
     */
    public static function getLicenseUsedByNode($owner_user_id, $node_id)
    {
        return self::findOne([
            'lic_srv_owner_user_id' => $owner_user_id,
            'lic_srv_node_id' => $node_id,
        ]);
    }

    /**
     * @param integer $owner_user_id
     * @param \common\models\UserNode $UserNode
     * @return static
     */
    public static function tryObtainLicenseByNode($owner_user_id, $UserNode)
    {
        $tryLicense = self::getLicenseUsedByNode($owner_user_id, $UserNode->node_id);
        if ($tryLicense) {
            if (strtotime($tryLicense->lic_srv_end) > time()) {
                return $tryLicense;
            } else {
                return false;
            }
        }

        $tryLicense = self::getFreeLicense($owner_user_id);
        if ($tryLicense) {
            $tryLicense->lic_srv_node_id = $UserNode->node_id;
            $tryLicense->lic_srv_colleague_user_id = $UserNode->user_id;
            if ($tryLicense->save()) {
                return $tryLicense;
            }
        }

        return false;
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
                  FROM {{%user_server_licenses}}
                  WHERE lic_srv_owner_user_id = :owner_user_id";
        $res = Yii::$app->db->createCommand($query, [
            'owner_user_id' => $owner_user_id,
        ])->queryOne();
        if (!isset($res['total'])) {
            $license['total'] = 0;
        } else {
            $license['total'] = $res['total'];
        }

        $query = "SELECT count(*) as unused
                  FROM {{%user_server_licenses}}
                  WHERE (lic_srv_owner_user_id = :owner_user_id)
                  AND (lic_srv_node_id IS NULL)";
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
     * @param integer $owner_user_id
     * @return array|null
     */
    public static function getNodesThatNeedServerLicense($owner_user_id)
    {
        $sql = "SELECT
                  (CASE WHEN (t1.user_id = :business_admin_user_id) THEN 1 ELSE 0 END) as user_own_node,
                  t1.node_id,
                  t1.user_id,
                  t1.is_server,
                  t2.lic_srv_id,
                  t2.lic_srv_start
                FROM {{%user_node}} as t1
                LEFT JOIN {{%user_server_licenses}} as t2 ON (t1.node_id = t2.lic_srv_node_id) AND (t2.lic_srv_owner_user_id = :business_admin_user_id)
                WHERE ((t1.user_id = :business_admin_user_id) OR (t1.user_id IN (
                  SELECT user_id FROM {{%user_colleagues}} WHERE (user_id!= :business_admin_user_id) AND (user_id IS NOT NULL) AND (collaboration_id IN (
                    SELECT collaboration_id FROM {{%user_collaborations}} WHERE user_id = :business_admin_user_id)
                  )
                )))
                AND (t1.is_server = :is_server)
                AND (t1.node_status NOT IN (:DELETED, :DEACTIVATED))
                AND NOT((t1.node_prev_status = :DEACTIVATED) AND (t1.node_status IN (:LOGGEDOUT, :WIPED)))
                AND t2.lic_srv_node_id IS NULL
                ORDER BY user_own_node DESC";
        $res = Yii::$app->db->createCommand($sql, [
            'business_admin_user_id' => $owner_user_id,
            'is_server'              => UserNode::IS_SERVER,
            'DELETED'                => UserNode::STATUS_DELETED,
            'DEACTIVATED'            => UserNode::STATUS_DEACTIVATED,
            'LOGGEDOUT'              => UserNode::STATUS_LOGGEDOUT,
            'WIPED'                  => UserNode::STATUS_WIPED,
        ])->queryAll();
        if (is_array($res) && sizeof($res)) {
            return $res;
        }

        return null;
    }

}
