<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%mail_templates}}".
 *
 * @property string $lang
 */
class Languages
{
    /**
     * returns list of avaible languages
     *
     * @return array
     */
    public static function langLabels()
    {
        return [
            'en' => Yii::t('models/languages', 'EN'),
            'de' => Yii::t('models/languages', 'DE'),
            'es' => Yii::t('models/languages', 'ES'),
            'ru' => Yii::t('models/languages', 'RU'),
        ];
    }

    /**
     * return lang name by lang value
     * @param string $lang
     *
     * @return string | null
     */
    public static function langLabel($lang)
    {
        $labels = self::langLabels();
        return isset($labels[$lang]) ? $labels[$lang] : $lang;
    }
}
