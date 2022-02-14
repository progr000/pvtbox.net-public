<?php

namespace common\models;

use Yii;
use yii\base\Model;
use common\helpers\Functions;

/**
 * This is the model class for table "{{%mail_templates}}".
 *
 * @property string $lang
 */
class Maintenance extends Model
{
    public
        $maintenance_show_empty_page,
        $maintenance_can_login,

        $maintenance_suspend_site,
        $maintenance_suspend_fm,
        $maintenance_suspend_api,
        $maintenance_suspend_share,
        $maintenance_suspend_blog,

        $maintenance_type,
        $maintenance_text,
        $maintenance_can_close,
        $maintenance_ttl,

        $maintenance_start,
        $maintenance_finish,
        $maintenance_left_int,
        $maintenance_start_int,
        $maintenance_finish_int;

    public static $array_types = [
        'error'   => 'error',
        'danger'  => 'danger',
        'success' => 'success',
        'info'    => 'info',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['maintenance_text', 'maintenance_type', 'maintenance_can_close', 'maintenance_can_login'], 'required'],
            [[
                'maintenance_can_close',
                'maintenance_can_login',
                'maintenance_show_empty_page',
                'maintenance_suspend_site',
                'maintenance_suspend_fm',
                'maintenance_suspend_api',
                'maintenance_suspend_share',
                'maintenance_suspend_blog',
            ], 'boolean'],
            [['maintenance_ttl'], 'integer', 'min' => 0],
            [['maintenance_ttl'], 'default', 'value' => 0],
            [['maintenance_start', 'maintenance_finish'], 'validateDateField', 'skipOnEmpty' => true],
            [['maintenance_start', 'maintenance_finish'], 'safe'],
            [['maintenance_text'], 'safe'],
            [['maintenance_type'], 'in', 'range' => self::$array_types],
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
            'maintenance_show_empty_page' => 'Show empty page',
            'maintenance_can_login'       => 'Can login',

            'maintenance_suspend_site'    => 'Suspend site',
            'maintenance_suspend_fm'      => 'Suspend FileManager',
            'maintenance_suspend_api'     => 'Suspend api',
            'maintenance_suspend_share'   => 'Suspend shares',
            'maintenance_suspend_blog'    => 'Suspend Blog',

            'maintenance_type'            => 'Type (color)',
            'maintenance_text'            => 'Text about maintaince',
            'maintenance_can_close'       => 'Can close',
            'maintenance_ttl'             => 'Auto close after N seconds',

            'maintenance_finish'          => 'Finish time',
            'maintenance_start'           => 'Start time',
        ];
    }

    /**
     *
     */
    public function setMaintenance()
    {
        $result = serialize([
            'maintenance_show_empty_page' => $this->maintenance_show_empty_page,
            'maintenance_can_login'       => $this->maintenance_can_login,

            'maintenance_suspend_site'    => $this->maintenance_suspend_site,
            'maintenance_suspend_fm'      => $this->maintenance_suspend_fm,
            'maintenance_suspend_api'     => $this->maintenance_suspend_api,
            'maintenance_suspend_share'   => $this->maintenance_suspend_share,
            'maintenance_suspend_blog'    => $this->maintenance_suspend_blog,

            'maintenance_type'            => $this->maintenance_type,
            'maintenance_text'            => $this->maintenance_text,
            'maintenance_can_close'       => $this->maintenance_can_close,
            'maintenance_ttl'             => $this->maintenance_ttl,

            'maintenance_start'           => time(),
            'maintenance_finish'          => strtotime($this->maintenance_finish),
        ]);

        return Preferences::setValueByKey('MaintenanceSettings', $result, Preferences::CATEGORY_HIDDEN, 'MaintenanceSettings');
    }

    /**
     * @return Maintenance
     */
    public static function getMaintenance()
    {
        $result = unserialize(Preferences::getValueByKey('MaintenanceSettings', serialize([]), 'string'));

        //var_dump($result);exit;
        $model = new Maintenance();
        //$model->load($result);

        $model->maintenance_show_empty_page = isset($result['maintenance_show_empty_page'])
            ? intval($result['maintenance_show_empty_page'])
            : 0;
        $model->maintenance_can_login       = isset($result['maintenance_can_login'])
            ? intval($result['maintenance_can_login'])
            : 1;

        $model->maintenance_suspend_site    = isset($result['maintenance_suspend_site'])
            ? intval($result['maintenance_suspend_site'])
            : 0;
        $model->maintenance_suspend_fm    = isset($result['maintenance_suspend_fm'])
            ? intval($result['maintenance_suspend_fm'])
            : 0;
        $model->maintenance_suspend_api     = isset($result['maintenance_suspend_api'])
            ? intval($result['maintenance_suspend_api'])
            : 0;
        $model->maintenance_suspend_share   = isset($result['maintenance_suspend_share'])
            ? intval($result['maintenance_suspend_share'])
            : 0;
        $model->maintenance_suspend_blog   = isset($result['maintenance_suspend_blog'])
            ? intval($result['maintenance_suspend_blog'])
            : 0;

        $model->maintenance_type            = isset($result['maintenance_type'])
            ? $result['maintenance_type']
            : self::$array_types['info'];
        $model->maintenance_text            = isset($result['maintenance_text'])
            ? $result['maintenance_text']
            : '';
        $model->maintenance_can_close       = isset($result['maintenance_can_close'])
            ? intval($result['maintenance_can_close'])
            : 1;
        $model->maintenance_ttl             = isset($result['maintenance_ttl'])
            ? intval($result['maintenance_ttl'])
            : 0;

        $model->maintenance_start           = isset($result['maintenance_start'])
            ? date('Y-m-d H:i', $result['maintenance_start'])
            : date('Y-m-d H:i');
        $model->maintenance_finish          = isset($result['maintenance_finish'])
            ? date('Y-m-d H:i', $result['maintenance_finish'])
            : date('Y-m-d H:i');
        $model->maintenance_start_int       = isset($result['maintenance_start'])
            ?  intval($result['maintenance_start'])
            : time();
        $model->maintenance_finish_int      = isset($result['maintenance_finish'])
            ? intval($result['maintenance_finish'])
            : time();
        $left = $model->maintenance_finish_int - time();
        $model->maintenance_left_int        = ($left > 0)
            ? $left
            : 0;

        //var_dump($model);exit;
        return $model;
    }

    /**
     * @param \common\models\Maintenance $Maintenance
     */
    public static function maintenanceFlash($Maintenance)
    {
        Yii::$app->session->removeFlash($Maintenance->maintenance_type . '-maintenance');
        Yii::$app->session->removeFlash('error-maintenance');
        Yii::$app->session->removeFlash('info-maintenance');
        Yii::$app->session->removeFlash('danger-maintenance');
        Yii::$app->session->removeFlash('warning-maintenance');
        Yii::$app->session->removeFlash('success-maintenance');
        Yii::$app->session->setFlash($Maintenance->maintenance_type . '-maintenance', [
            'message' => nl2br(str_replace([
                '{maintenance_start}',
                '{maintenance_finish}',
                '{maintenance_left}',
            ], [
                date(Yii::$app->params['datetime_short_format'], $Maintenance->maintenance_start_int),
                date(Yii::$app->params['datetime_short_format'], $Maintenance->maintenance_finish_int),
                Functions::getHumanReadableLeftTime($Maintenance->maintenance_left_int),
            ], $Maintenance->maintenance_text)),
            'type' => $Maintenance->maintenance_type,
            'ttl' => $Maintenance->maintenance_ttl * 1000,
            'showClose' => $Maintenance->maintenance_can_close,
        ]);
    }
}
