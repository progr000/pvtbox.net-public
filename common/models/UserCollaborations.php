<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%user_collaborations}}".
 *
 * @property string $collaboration_id
 * @property integer $collaboration_status
 * @property string $file_uuid
 * @property string $user_id
 * @property string $collaboration_created
 *
 * @property Users $user
 */
class UserCollaborations extends ActiveRecord
{
    private static $CACHE_TTL = 3600;

    const STATUS_ACTIVE            = 1;
    const STATUS_DEACTIVATED       = 0;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'collaboration_created',
                'updatedAtAttribute' => null,
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_collaborations}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'], // added+++ 2019-03-07 13:00

            [['user_id'], 'integer'],

            [['collaboration_status'], 'integer'],
            [['collaboration_status'], 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_DEACTIVATED,
            ]], // added+++ 2019-03-07 13:00
            [['collaboration_status'], 'default', 'value' => self::STATUS_ACTIVE],

            [['collaboration_created'], 'validateDateField', 'skipOnEmpty' => true],
            [['collaboration_created'], 'safe'],

            [['file_uuid'], 'string', 'length' => 32], // changed+++ 2019-03-07 13:00

            /* unique keys */
            [['file_uuid'], 'unique', 'skipOnEmpty' => true],

            /* foreign keys */
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'collaboration_id' => 'ID',
            'collaboration_status' => 'Status of collaboration 1=active, 0=>deactivated',
            'file_uuid' => 'file_uuid',
            'user_id' => 'UserID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(UserFiles::className(), ['user_id' => 'user_id', 'file_uuid' => 'file_uuid']);
    }

    /**
     * @param int|string $id
     * @return UserCollaborations|null
     */
    public static function findIdentity($id)
    {

        return self::getDb()->cache(
            function($db) use($id) {
                return static::findOne(['collaboration_id' => $id]);
            },
            self::$CACHE_TTL,
            new TagDependency(['tags' => 'UserCollaborations.collaboration_id.' . $id])
        );

        //return static::findOne(['collaboration_id' => $id]);
    }

    /**
     * @param $collaboration_id
     * @return int|null
     */
    public static function getOwner($collaboration_id)
    {
        $UserCollaboration = self::findIdentity($collaboration_id);
        if ($UserCollaboration) {
            return (int) $UserCollaboration->user_id;
        } else {
            return null;
        }
    }

    /**
     * Invalidate Cache
     */
    protected function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'UserCollaborations.collaboration_id.' . $this->collaboration_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->invalidateCache();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $this->invalidateCache();
    }

}
