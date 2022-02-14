<?php

namespace common\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%preferences}}".
 *
 * @property string $pref_id
 * @property string $pref_title
 * @property string $pref_key
 * @property string $pref_value
 * @property integer $pref_category
 */
class Preferences extends ActiveRecord
{
    const CATEGORY_BASE      = 1;
    const CATEGORY_OTHER     = 2;
    const CATEGORY_RECAPTCHA = 3;
    const CATEGORY_NODEAPI   = 4;
    const CATEGORY_PRICES    = 5;
    const CATEGORY_SEO       = 6;
    const CATEGORY_HIDDEN    = 100;

    /**
     * returns list of categories in array
     *
     * @return array
     */
    public static function categoriesLabels()
    {
        if (Yii::$app->params['self_hosted']) {
            return [
                self::CATEGORY_BASE      => 'Base',
                self::CATEGORY_RECAPTCHA => 'Recaptcha',
                self::CATEGORY_NODEAPI   => 'API',
            ];
        }

        return [
            self::CATEGORY_BASE      => 'Base',
            self::CATEGORY_RECAPTCHA => 'Recaptcha',
            self::CATEGORY_NODEAPI   => 'API',
            self::CATEGORY_PRICES    => 'Licenses',
            self::CATEGORY_SEO       => 'Seo',
            self::CATEGORY_OTHER     => 'Other',
            //self::CATEGORY_HIDDEN    => 'Hidden',
        ];
    }

    /**
     * return category name by pref_category value
     * @param integer $pref_category
     *
     * @return string | null
     */
    public static function categoryLabel($pref_category)
    {
        $labels = self::categoriesLabels();
        return isset($labels[$pref_category]) ? $labels[$pref_category] : null;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%preferences}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pref_title', 'pref_key'], 'required'],
            //['pref_category', 'integer', 'min'=>self::CATEGORY_BASE, 'max'=>self::CATEGORY_RECAPTCHA],
            ['pref_category', 'integer', 'min'=>self::CATEGORY_BASE],
            ['pref_category', 'default', 'value' => self::CATEGORY_BASE],
            [['pref_key', 'pref_value'], 'trim'],
            [['pref_title'], 'string', 'max' => 255],
            [['pref_value'], 'safe'],
            [['pref_key'], 'string', 'max' => 50],
            [['pref_key'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */

    /**
     * @param int|string $id
     * @return Preferences|null
     */
    public static function findIdentity($id)
    {
        return static::findOne(['pref_id' => $id]);
    }

    /**
     * Finds preferences by pref_key
     *
     * @param string $pref_key
     * @return Preferences|null
     */
    public static function findByKey($pref_key)
    {
        return self::getDb()->cache(
            function($db) use($pref_key)
            {
                return static::findOne(['pref_key' => $pref_key]);
            },
            null,
            new TagDependency(['tags' => 'preferences.' . $pref_key])
        );
    }

    /**
     * @param string $pref_key
     * @param string $pref_value
     * @param integer|null $pref_category
     * @param string|null $pref_title
     * @return bool
     */
    public static function setValueByKey($pref_key, $pref_value, $pref_category=null, $pref_title=null)
    {
        $test = self::categoriesLabels();
        if (!isset($pref_category)) { $pref_category = self::CATEGORY_HIDDEN; }
        if (!isset($test[$pref_category])) { $pref_category = self::CATEGORY_HIDDEN; }

        $pref = self::findByKey($pref_key);
        if (!$pref) {
            $pref = new Preferences();
            $pref->pref_key = $pref_key;
        }
        $pref->pref_value    = $pref_value;
        $pref->pref_category = $pref_category;
        $pref->pref_title    = isset($pref_title) ? $pref_title : $pref_key;

        return $pref->save();
    }

    /**
     * @param string $pref_key
     * @param mixed $default
     * @param string $type
     * @return float|int|null|string
     */
    public static function getValueByKey($pref_key, $default=null, $type='string')
    {
        $pref = self::findByKey($pref_key);
        if ($pref) {

            if (in_array($type, ['int', 'integer']))
                return intval($pref->pref_value);

            if (in_array($type, ['double', 'decimal', 'float'])) {
                $pref->pref_value = str_replace(',', '.', $pref->pref_value);
                return doubleval($pref->pref_value);
            }

            return $pref->pref_value;

        } else {
            return $default;
        }
    }

    /**
     * @return string
     */
    public static function getJsStringForPricing()
    {
        $str  = "\n";
        $str .= "var PriceOneTimeForLicenseProfessional  = " . self::getValueByKey('PriceOneTimeForLicenseProfessional', 99.99, 'float') . ";\n";
        $str .= "var PricePerMonthForLicenseProfessional = " . self::getValueByKey('PricePerMonthForLicenseProfessional', 99.99, 'float') . ";\n";
        $str .= "var PricePerMonthUserForLicenseBusiness = " . self::getValueByKey('PricePerMonthUserForLicenseBusiness', 99.99, 'float') . ";\n";
        $str .= "var PricePerYearForLicenseProfessional  = " . self::getValueByKey('PricePerYearForLicenseProfessional', 99.99, 'float') . ";\n";
        $str .= "var PricePerYearUserForLicenseBusiness  = " . self::getValueByKey('PricePerYearUserForLicenseBusiness', 99.99, 'float') . ";\n";

        $str .= "var PricePerMonthForServerLicenseBusiness = 79.99;\n";
        $str .= "var PricePerYearForServerLicenseBusiness = 66.65;\n";

        //$str .= "var link_professional = '/purchase/professional';\n";
        //$str .= "var link_business = '/purchase/business';\n";
        $str .= "var link_professional = '/purchase/summary?license=professional';\n";
        $str .= "var link_business = '/purchase/summary?license=business';\n";
        return $str;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(Yii::$app->cache, 'preferences.' . $this->pref_key);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        TagDependency::invalidate(Yii::$app->cache, 'preferences.' . $this->pref_key);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pref_id' => 'ID',
            'pref_title' => 'Title',
            'pref_key' => 'KEY',
            'pref_value' => 'Value',
            'pref_category' => 'Category',
        ];
    }
}
