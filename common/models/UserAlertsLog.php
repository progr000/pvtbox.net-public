<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%user_alerts_log}}".
 *
 * @property int $record_id ID
 * @property string $alert_created Date
 * @property string $alert_url Url of page, where alert is coming
 * @property string $alert_message Alert text
 * @property int $alert_close_button Close button is showed or hidden for alert
 * @property int $alert_ttl Time while alert is showing
 * @property string $alert_view_type Type of alert window (flash or snack)
 * @property string $alert_type Type of alert (danger, warning or notice)
 * @property string $alert_action Action which caused alert
 * @property resource $alert_screen Screenshot
 * @property int $user_id UserID
 *
 * @property Users $user
 */
class UserAlertsLog extends ActiveRecord
{
    protected static $CACHE_TTL = 3600;

    const VIEW_SNACK = 'snack';
    const VIEW_FLASH = 'flash';

    const TYPE_ERROR   = 'error';
    const TYPE_DANGER  = 'danger';
    const TYPE_SUCCESS = 'success';
    const TYPE_UNKNOWN = 'unknown';

    const CLOSE_ENABLED  = 1;
    const CLOSE_DISABLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_alerts_log}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'alert_created',
                'updatedAtAttribute' => null,
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
            [['alert_url', 'alert_message', 'alert_view_type', 'alert_type'], 'required'],
            [['alert_created'], 'safe'],
            [['alert_message', 'alert_screen'], 'string'],
            [['alert_close_button', 'alert_ttl'], 'default', 'value' => 0],
            [['user_id'], 'default', 'value' => null],
            [['alert_close_button', 'alert_ttl', 'user_id'], 'integer'],
            [['alert_close_button'], 'in', 'range' => [self::CLOSE_DISABLED, self::CLOSE_ENABLED]],
            [['alert_url'], 'string', 'max' => 255],
            [['alert_view_type'], 'in', 'range' => [self::VIEW_SNACK, self::VIEW_FLASH]],
            [['alert_type'], 'in', 'range' => [self::TYPE_ERROR, self::TYPE_DANGER, self::TYPE_SUCCESS, self::TYPE_UNKNOWN]],
            [['alert_action'], 'string', 'max' => 100],
            [['alert_action'], 'default', 'value' => null],
            //[['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'record_id' => 'ID',
            'alert_created' => 'Date',
            'alert_url' => 'Url of page, where alert is coming',
            'alert_message' => 'Alert text',
            'alert_close_button' => 'Close button is showed or hidden for alert',
            'alert_ttl' => 'Time while alert is showing',
            'alert_view_type' => 'Type of alert window (flash or snack)',
            'alert_type' => 'Type of alert (danger, warning or notice)',
            'alert_action' => 'Action which caused alert',
            'alert_screen' => 'Screenshot',
            'user_id' => 'UserID',
        ];
    }

    /**
     * @param int|string $id
     * @return static
     */
    public static function findIdentity($id)
    {
        return self::getDb()->cache(
            function($db) use($id) {
                return static::findOne(['record_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserAlertsLog.record_id.' . $id])
        );
        //return static::findOne(['node_id' => $id]);
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ERROR   => self::TYPE_ERROR,
            self::TYPE_DANGER  => self::TYPE_DANGER,
            self::TYPE_SUCCESS => self::TYPE_SUCCESS,
        ];
    }

    /**
     * @return array
     */
    public static function getViewTypes()
    {
        return [
            self::VIEW_SNACK => self::VIEW_SNACK,
            self::VIEW_FLASH => self::VIEW_FLASH,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public static function getActions()
    {
        $ret = self::getDb()->cache(
            function($db) {
                return self::find()
                    ->select('alert_action')
                    ->where('alert_action IS NOT NULL')
                    ->groupBy('alert_action')
                    ->orderBy('alert_action')
                    ->asArray()
                    ->all();
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserAlertsLog'])
        );

        $arr =  [];
        foreach ($ret as $v) {
            $arr[$v['alert_action']] = $v['alert_action'];
        }

        return $arr;
    }

    /**
     * Invalidate Cache
     */
    public static function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'UserAlertsLog',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        /* clear cache */
        TagDependency::invalidate(Yii::$app->cache, [
            'UserAlertsLog.record_id.' . $this->record_id,
        ]);
        self::invalidateCache();

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }
}
