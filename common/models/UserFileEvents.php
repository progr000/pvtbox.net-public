<?php

namespace common\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_file_events}}".
 *
 * @property string $event_id
 * @property string $event_uuid
 * @property integer $event_type
 * @property integer $event_timestamp
 * @property integer $event_invisible
 * @property string $last_event_id
 * @property string $diff_file_uuid
 * @property integer $diff_file_size
 * @property string $rev_diff_file_uuid
 * @property integer $rev_diff_file_size
 * @property string $file_id
 * @property string $file_hash_before_event
 * @property string $file_hash
 * @property string $file_name_before_event
 * @property string $file_name_after_event
 * @property integer $file_size_before_event
 * @property integer $file_size_after_event
 * @property string $node_id
 * @property string $user_id
 * @property integer $erase_nested
 * @property integer $parent_before_event
 * @property integer $parent_after_event
 * @property integer $prev_event_timestamp
 * @property integer $prev_event_type
 * @property integer $is_rollback
 * @property string $event_creator_node_id
 * @property string $event_creator_user_id
 * @property integer $event_group_timestamp
 * @property integer $event_group_id
 *
 * @property UserFiles $file
 * @property UserNode $node
 */
class UserFileEvents extends ActiveRecord
{
    const TYPE_CREATE   = 0;
    const TYPE_UPDATE   = 1;
    const TYPE_DELETE   = 2;
    const TYPE_MOVE     = 3;
    const TYPE_FORK     = 4;
    const TYPE_RESTORE  = 5;
    const TYPE_ROLLBACK = 6;
    //const TYPE_SHARE   = 5;
    //const TYPE_UNSHARE = 6;

    const EVENT_INVISIBLE = 1;
    const EVENT_VISIBLE   = 0;

    const ERASE_NESTED_TRUE  = 1;
    const ERASE_NESTED_FALSE = 0;

    const IS_ROLLBACK  = 1;
    const NOT_ROLLBACK = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_file_events}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_uuid', 'file_id', 'user_id', 'last_event_id', 'event_timestamp', 'event_type'], 'required'], // added+++ 2019-03-06 15:30

            [[
                'event_uuid',
                'diff_file_uuid',
                'rev_diff_file_uuid',
                'file_hash',
                'file_hash_before_event'
            ], 'string', 'length' => 32], // changed+++ 2019-03-06 15:30  max=>length

            [['file_name_before_event', 'file_name_after_event'], 'string', 'max' => UserFiles::FILE_NAME_MAX_LENGTH],

            [[
                'file_id',
                'node_id',
                'user_id',
                'event_timestamp',
                'last_event_id',
                'diff_file_size',
                'rev_diff_file_size',
                'file_size_before_event',
                'file_size_after_event',
                'parent_before_event',
                'parent_after_event',
                'prev_event_timestamp', // Не используются почему то. Наверное не нужны.
                'prev_event_type',      // Вероятно деприкейтед. По коду нигде не нашел использования.
                'event_creator_user_id',
                'event_creator_node_id',
                'event_group_timestamp',
                'event_group_id'
            ], 'integer'],

            [['event_type'], 'integer'],
            [['event_type'], 'in', 'range' => [
                self::TYPE_CREATE,
                self::TYPE_UPDATE,
                self::TYPE_DELETE,
                self::TYPE_MOVE,
                self::TYPE_FORK,
                self::TYPE_RESTORE,
                self::TYPE_ROLLBACK,
            ]], // added+++ 2019-03-06 15:30

            [['event_invisible'], 'integer'],
            [['event_invisible'], 'in', 'range' => [self::EVENT_INVISIBLE, self::EVENT_VISIBLE]],
            [['event_invisible'], 'default', 'value' => self::EVENT_VISIBLE],

            [['erase_nested'], 'integer'],
            [['erase_nested'], 'in', 'range' => [self::ERASE_NESTED_TRUE, self::ERASE_NESTED_FALSE]],
            [['erase_nested'], 'default', 'value' => self::ERASE_NESTED_FALSE],

            [['is_rollback'], 'integer'],
            [['is_rollback'], 'in', 'range' => [self::IS_ROLLBACK, self::NOT_ROLLBACK]],
            [['is_rollback'], 'default', 'value' => self::NOT_ROLLBACK],

            /* defaults */
            //[['diff_file_size', 'rev_diff_file_size'], 'default', 'value' => 0], // removed+++ 2019-03-06 15:30

            /* unique keys */
            [['event_uuid', 'user_id'], 'unique', 'targetAttribute' => ['event_uuid', 'user_id'], 'message' => 'The combination of [event_uuid, user_id] has already been taken.'],
            [['file_id', 'last_event_id'], 'unique', 'targetAttribute' => ['file_id', 'last_event_id'], 'message' => 'The combination of [file_id, last_event_id] has already been taken.'],

            /* foreign keys */
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserFiles::className(), 'targetAttribute' => ['file_id' => 'file_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']], // added+++ 2019-03-06 15:30
            [['node_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']], // changed+++ 2019-03-06 15:30
            [['event_creator_user_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']], // added+++ 2019-03-12 15:30
            [['event_creator_node_id'], 'exist', 'skipOnEmpty' => true, 'skipOnError' => true, 'targetClass' => UserNode::className(), 'targetAttribute' => ['node_id' => 'node_id']], // added+++ 2019-03-12 15:30

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'event_id' => 'Id',
            'event_uuid' => 'Uuid',
            'event_type' => 'Type',
            'event_timestamp' => 'отметка времени. Должна выставляться сервером в момент регистрации события',
            'event_invisible' => 'признак невидимости евента в пользовательском интерфейсе отображения патчей',
            'last_event_id' => 'ссылка на предыдущее событие. Получаем от ноды. Должно быть null для события create и не null для всех остальных',
            'diff_file_uuid' => 'uuid файла с разницей данных, может быть null',
            'diff_file_size' => 'размер файла с разницей данных, not null. Может быть равным 0',
            'rev_diff_file_uuid' => 'uuid rev-файла с разницей данных, может быть null',
            'rev_diff_file_size' => 'размер rev-файла с разницей данных, not null. Может быть равным 0',
            'file_id' => 'Foreign key user_files.file_id',
            'file_hash_before_event' => 'File md5 before event',
            'file_hash' => 'File md5 after event',
            'file_name_before_event' => 'File name before event',
            'file_name_after_event' => 'File name after event',
            'file_size_before_event' => 'File size before event',
            'file_size_after_event' => 'File name after event',
            'node_id' => 'NodeID',
            'user_id' => 'UserID',
            'event_creator_user_id' => 'Реальный создатель евента (нужно для коллабораций)',
            'event_creator_node_id' => 'Реальная нода создателя евента (нужно для коллабораций)',
            'erase_nested' => 'Признак удаления всех чайлдов при выполнении этого евента',
            'parent_before_event' => 'Родитель елемента до выполнения евента',
            'parent_after_event'  => 'Родитель елемента после выполнения евента',
            'is_rollback' => 'Признак того что текущий евент является откатом',
            'event_group_timestamp' => 'признак что евент принадлежит группе копирования (у таких этозначение одинаково)',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(UserFiles::className(), ['file_id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(UserNode::className(), ['node_id' => 'node_id']);
    }

    /**
     * return list of types in array
     * @return array
     */
    public static function eventTypes()
    {
        return [
            self::TYPE_CREATE   => 'create',
            self::TYPE_UPDATE   => 'update',
            self::TYPE_DELETE   => 'delete',
            self::TYPE_MOVE     => 'move',
            self::TYPE_FORK     => 'fork',
            self::TYPE_RESTORE  => 'restore',
            self::TYPE_ROLLBACK => 'rollback',
            //self::TYPE_SHARE   => 'share',
            //self::TYPE_UNSHARE => 'unshare',
        ];
    }

    /**
     * return Name of event_type
     * @param integer $event_type
     * @return string | null
     */
    public static function getType($event_type)
    {
        $labels = self::eventTypes();
        return isset($labels[$event_type]) ? $labels[$event_type] : null;
    }

    public static function displayType($event_type)
    {
        $labels = self::eventTypes();
        return Yii::t('models/file-events', $labels[$event_type]);
    }

    /**
     * Invalidate Cache
     */
    protected function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, [
            'NodeApi.file_events__user_id_' . $this->user_id,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        /*
        if ($insert) {
            UserFiles::updateAll([
                'first_event_id' => $this->event_id,
                'last_event_id'  => $this->event_id,
            ], [
                'file_id' => $this->file_id
            ]);
        } else {
            //UserFiles::updateAll(['last_event_id'  => $this->event_id], ['file_id' => $this->file_id]);
        }
        */

        $this->invalidateCache();
    }
}
