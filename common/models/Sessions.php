<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%sessions}}".
 *
 * @property string $sess_id
 * @property string $sess_countrycode
 * @property string $sess_country
 * @property string $sess_city
 * @property string $sess_useragent
 * @property integer $sess_ip
 * @property string $sess_action
 * @property string $sess_created
 * @property string $user_id
 * @property string $node_id
 *
 * @property Users $user
 */
class Sessions extends ActiveRecord
{
    const ACTION_LOGIN      = 'login';
    const ACTION_REGISTER   = 'signup';
    const ACTION_LOGOUT     = 'logout';
    const ACTION_DELETE     = 'delete-account';
    const ACTION_DELETE_SHU = 'delete-shu-account';
    const ACTION_NODEAPI    = 'node';
    const ACTION_ADDNODE    = 'addnode';

    /**
     * returns list of statuses in array
     * @return array
     */
    public static function actionLabels()
    {
        return [
            self::ACTION_LOGIN    => Yii::t('models/sessions', 'successful-login'),
            self::ACTION_REGISTER => Yii::t('models/sessions', 'successful-signup'),
            self::ACTION_LOGOUT   => Yii::t('models/sessions', 'successful-logout'),
            self::ACTION_DELETE   => Yii::t('models/sessions', 'delete-account'),
            self::ACTION_DELETE   => Yii::t('models/sessions', 'delete-shu-account'),
            self::ACTION_NODEAPI  => Yii::t('models/sessions', 'access-api'),
            self::ACTION_ADDNODE  => Yii::t('models/sessions', 'add-node'),
        ];
    }

    /**
     * return status name by transfer_status value
     * @param string $sess_action
     * @return string | null
     */
    public static function actionLabel($sess_action)
    {
        $labels = self::actionLabels();
        return isset($labels[$sess_action]) ? $labels[$sess_action] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sessions}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'sess_created',
                'updatedAtAttribute' => null,
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
            [['user_id', 'sess_action'], 'required'],
            [['sess_action'], 'string', 'max' => 30],
            [['sess_ip', 'user_id'], 'integer'],
            [['sess_countrycode'], 'string', 'max' => 2],
            [['sess_country', 'sess_city'], 'string', 'max' => 40],
            //[['sess_useragent'], 'string', 'max' => 255],
            [['sess_useragent'], 'cut', 'skipOnEmpty' => false, 'skipOnError' => false],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function cut($attribute, $params)
    {
        //if (isset($params['length'])) {
            $this->sess_useragent = substr($this->sess_useragent, 0, 255);
        //}
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sess_id'          => 'ID',
            'sess_countrycode' => Yii::t('app/pages', 'Country Code'),
            'sess_country'     => Yii::t('app/pages', 'Country'),
            'sess_city'        => Yii::t('app/pages', 'City'),
            'sess_useragent'   => Yii::t('app/pages', 'Application'),
            'sess_ip'          => Yii::t('app/pages', 'IP Adress'),
            'sess_action'      => Yii::t('app/pages', 'Status'),
            'sess_created'     => Yii::t('app/pages', 'Last activity'),
            'user_id'          => 'UserID',
            'node_id'          => 'NodeID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(UserNode::className(), ['node_id' => 'node_id']);
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
        if (parent::beforeSave($insert)) {

            if (!$this->sess_ip) {
                $this->sess_ip = Yii::$app->request->getUserIP();
            }

            if (!$this->sess_useragent) {
                $this->sess_useragent = Yii::$app->request->getUserAgent();
            }
            if (!$this->sess_useragent) {
                $this->sess_useragent = 'Unknown';
            }

            $Info = \Yii::createObject([
                'class' => '\rmrevin\yii\geoip\HostInfo',
                'host' => $this->sess_ip,
            ]);

            if ($Info->isAvailable()) {
                $InfoData = $Info->getData();
                $this->sess_city        = !empty($InfoData['city'])         ? mb_substr(mb_convert_encoding($InfoData['city'],         'UTF-8'), 0, 40) : '';
                $this->sess_country     = !empty($InfoData['country_name']) ? mb_substr(mb_convert_encoding($InfoData['country_name'], 'UTF-8'), 0, 40) : '';
                $this->sess_countrycode = !empty($InfoData['country_code']) ? mb_substr(mb_convert_encoding($InfoData['country_code'], 'UTF-8'), 0, 2)  : '';
            }

            // +++ IP to long
            if (is_string($this->sess_ip)) {
                $this->sess_ip = intval(ip2long($this->sess_ip));
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->sess_ip = long2ip($this->sess_ip);
    }
}
