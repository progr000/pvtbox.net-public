<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%pages}}".
 *
 * @property string $page_id
 * @property string $page_created
 * @property string $page_updated
 * @property integer $page_status
 * @property string $page_lang
 * @property string $page_title
 * @property string $page_name
 * @property string $page_alias
 * @property string $page_keywords
 * @property string $page_description
 * @property string $page_text
 * @property string $_old_page_alias
 * @property string $_old_page_lang
 */
class Pages extends ActiveRecord
{
    const STATUS_ACTIVE   = 1;
    const STATUS_DEACTIVE = 0;

    public $_old_page_alias;
    public $_old_page_lang;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_name', 'page_text', 'page_alias', 'page_lang'], 'required'],
            [['page_lang'], 'string', 'max' => 3],
            [['page_created', 'page_updated'], 'safe'],
            [['page_status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DEACTIVE]],
            [['page_status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['page_text'], 'safe'],
            [['page_title', 'page_alias', 'page_keywords', 'page_description'], 'string', 'max' => 255],
            [['page_name'], 'string', 'max' => 100],
            [['page_alias', 'page_lang'], 'unique', 'targetAttribute' => ['page_alias', 'page_lang'], 'message' => 'The combination of Язык страницы and ЧПУ has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'page_id' => 'Id',
            'page_created' => 'Creation Date',
            'page_updated' => 'Updated Date',
            'page_status' => 'Status',
            'page_lang' => 'Language',
            'page_title' => 'SEO-title',
            'page_name' => 'Title',
            'page_alias' => 'RewriteURL',
            'page_keywords' => 'SEO-keywords',
            'page_description' => 'SEO-description',
            'page_text' => 'Text',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'page_created',
                'updatedAtAttribute' => 'page_updated',
                'value' => function() { return date(SQL_DATE_FORMAT); }
            ],
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_old_page_alias = $this->page_alias;
        $this->_old_page_lang  = $this->page_lang;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            $this->invalidateCache();

            if (empty($this->page_alias)) {
                //$this->page_alias = \common\helpers\TextHelper::rus2SmallTransliteration($this->page_name);
            }

            if (empty($this->page_title)) {
                $this->page_title = $this->page_name;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Invalidate Cache
     */
    protected function invalidateCache()
    {
        $old_tag_key = md5( 'page' . $this->_old_page_alias . $this->_old_page_lang );
        $tag_key = md5( 'page' . $this->page_alias . $this->page_lang );
        TagDependency::invalidate(Yii::$app->cache, [$tag_key, $old_tag_key]);
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

    /**
     * returns list of actives in array
     *
     * @return array
     */
    public static function statuses()
    {
        return [
            self::STATUS_ACTIVE   => 'Published',
            self::STATUS_DEACTIVE => 'Draft',
        ];
    }

    /**
     * return status
     * @param integer $link_status
     *
     * @return string | null
     */
    public static function getStatus($link_status)
    {
        $labels = self::Statuses();
        return isset($labels[$link_status]) ? $labels[$link_status] : null;
    }

    /**
     * @param string|null $alias
     * @return array
     */
    public static function pagesBreadcrumbs($alias=null)
    {
        /* @var $model \common\models\Pages */
        $pages = Pages::find()
            //->asArray()
            ->select(['page_alias', 'page_title', 'page_name'])
            ->where([
                'page_status' => Pages::STATUS_ACTIVE,
                'page_lang' => Yii::$app->language
            ])
            ->all();

        $breadcrumbs = [];
        if ($pages) {
            foreach ($pages as $model) {
                if ($alias == $model->page_alias) {
                    $breadcrumbs[] = ($model->page_title ? $model->page_title : ($model->page_name ? $model->page_name : $model->page_alias));
                } else {
                    $breadcrumbs[] = [
                        'label' => ($model->page_title ? $model->page_title : ($model->page_name ? $model->page_name : $model->page_alias)),
                        'url' => ['page/' . $model->page_alias],
                    ];
                }
            }
        }

        return $breadcrumbs;
    }
}
