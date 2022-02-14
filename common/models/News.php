<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%news}}".
 *
 * @property string $news_id
 * @property string $news_name
 * @property string $news_text
 * @property integer $news_status
 * @property string $news_created
 * @property string $news_updated
 */
class News extends ActiveRecord
{
    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVE = 2;
    const STATUS_HIDDEN = 3;

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function statusParams()
    {
        return [
            self::STATUS_BLOCKED => ['name' => 'Unpublished', 'color' => '#BB0202'],
            self::STATUS_ACTIVE  => ['name' => 'Published',    'color' => '#25BB02'],
            self::STATUS_ARCHIVE => ['name' => 'Archival',        'color' => '#CACC01'],
            self::STATUS_HIDDEN  => ['name' => 'Hidden',         'color' => '#7092BE'],
        ];
    }

    /**
     * returns list of statuses in array
     *
     * @return array
     */
    public static function statusLabels()
    {
        $labels = [];
        $params = self::statusParams();
        foreach ($params as $k=>$v)
            $labels[$k] = $v['name'];

        return $labels;
    }

    /**
     * return category name by pref_category value
     * @param integer $news_status
     *
     * @return string | null
     */
    public static function statusLabel($news_status)
    {
        $params = self::statusParams();
        return isset($params[$news_status]) ? $params[$news_status]['name'] : null;
    }

    /**
     * return status-color by news_status value
     * @param integer $news_status
     *
     * @return string | null
     */
    public static function statusColor($news_status)
    {
        $params = self::statusParams();
        return isset($params[$news_status]) ? $params[$news_status]['color'] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%news}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'news_created',
                'updatedAtAttribute' => 'news_updated',
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
            [['news_name', 'news_text'], 'required'],
            [['news_name', 'news_text'], 'trim'],
            [['news_status'], 'integer'],
            [['news_name'], 'string', 'max' => 255],
            ['news_status', 'integer', 'min' => self::STATUS_BLOCKED, 'max' => self::STATUS_HIDDEN],
            ['news_status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'news_id' => 'Id',
            'news_name' => 'Title',
            'news_text' => 'News text',
            'news_status' => 'Status',
            'news_created' => 'Creation Date',
            'news_updated' => 'Updated Date',
        ];
    }

    /**
     * @param int|string $id
     * @return News|null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['news_id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
}
