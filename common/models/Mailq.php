<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%mailq}}".
 *
 * @property int $mail_id ID
 * @property string $mail_created Date
 * @property string $mail_from
 * @property string $mail_to
 * @property string $mail_reply_to
 * @property string $mail_subject
 * @property string $mail_body
 * @property string $mailer_letter_id Unique id of letter on mailer system
 * @property string $mailer_answer Mailer answer on try letter send
 * @property string $mailer_letter_status Letter status on mailer system
 * @property string $mailer_description Description for letter status from mailer
 * @property int $user_id UserID link to users.user_id
 * @property int $node_id NodeID link to user_node.node_id
 * @property string $template_key
 * @property integer $remote_ip
 *
 * @property UserNode $node
 * @property Users $user
 */
class Mailq extends ActiveRecord
{
    protected static $CACHE_TTL = 3600;

    const STATUS_ERROR    = "error";
    const STATUS_QUEUED   = "queued";
    const STATUS_SENT     = "sent";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mailq}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'mail_created',
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
            [['mail_from', 'mail_to', 'mail_subject', 'mail_body', 'mailer_answer', 'mailer_letter_status'], 'required'],
            [['mail_created'], 'safe'],
            [['mail_from', 'mail_to', 'mail_reply_to', 'mail_subject', 'mail_body', 'mailer_answer', 'mailer_description'], 'string'],
            [['user_id', 'node_id'], 'default', 'value' => null],
            [['user_id', 'node_id'], 'integer'],
            [['mailer_letter_id', 'mailer_letter_status'], 'string', 'max' => 32],
            [['template_key'], 'string', 'max' => 100],
            [['mailer_letter_id'], 'unique'],
            //[['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']],
            //[['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mail_id' => 'ID',
            'mail_created' => 'Date',
            'mail_from' => 'From',
            'mail_to' => 'To',
            'mail_reply_to' => 'Reply To',
            'mail_subject' => 'Subject',
            'mail_body' => 'Body',
            'mailer_letter_id' => 'Unique letter ID on mailer system',
            'mailer_answer' => 'Answer on passing letter to mailer-system',
            'mailer_letter_status' => 'Letter status',
            'mailer_description' => 'Description for letter status from mailer',
            'user_id' => 'UserID link to users.user_id',
            'node_id' => 'NodeID link to user_node.node_id',
            'template_key' => 'Template Key',
            'remote_ip' => 'remote_ip',
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
     * @return array
     */
    public static function mailqStatuses()
    {
        $ret = self::getDb()->cache(
            function($db) {
                return self::find()
                    ->select('mailer_letter_status')
                    ->groupBy('mailer_letter_status')
                    ->orderBy('mailer_letter_status')
                    ->asArray()
                    ->all();
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'Mailq'])
        );

        $arr =  [
            self::STATUS_SENT     => self::STATUS_SENT,
            self::STATUS_QUEUED   => self::STATUS_QUEUED,
            self::STATUS_ERROR    => self::STATUS_ERROR,
        ];
        foreach ($ret as $v) {
            $arr[$v['mailer_letter_status']] = $v['mailer_letter_status'];
        }

        return $arr;
    }

    /**
     * Invalidate Cache
     */
    public static function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'Mailq',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        self::invalidateCache();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if (!$this->remote_ip) {
                if (method_exists(Yii::$app->request, 'getUserIP')) {
                    $this->remote_ip = Yii::$app->request->getUserIP();
                } else {
                    $this->remote_ip = '127.0.0.1';
                }
            }

            if (is_string($this->remote_ip)) {
                $this->remote_ip = intval(ip2long($this->remote_ip));
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
        $this->remote_ip = long2ip($this->remote_ip);
    }
}
