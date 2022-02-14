<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\Licenses;

/**
 * Profile form
 *
 * @property string $user_name
 *
 */
class SetTimeZoneOffsetForm extends Model
{
    public $timezone_offset_seconds;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['timezone_offset_seconds', 'required'],
            ['timezone_offset_seconds', 'integer',  'min' => -43200, 'max' => 46800],
        ];
    }

    /**
     * attribute for input fields.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'timezone_offset_seconds' => Yii::t('forms/timezone', 'timezone_offset_seconds'),
        ];
    }

    /**
     * Change TZ for User
     * @param \common\models\Users $User
     * @return bool
     */
    public function setDynamicTimeZone($User)
    {
        /* Откоментировать эту строку, если вдруг захотим нормальное время юзеру показывать его локальное не зависимо от выбранной им таймзоны */
        //Yii::$app->session->set('UserTimeZoneOffset', $this->timezone_offset_seconds);

        if ($User->static_timezone != 0) {
            //Yii::$app->session->set('UserTimeZoneOffset', $User->static_timezone);
        }
        if ($User->dynamic_timezone > $this->timezone_offset_seconds + 10 || $User->dynamic_timezone < $this->timezone_offset_seconds - 10) {
            $User->dynamic_timezone = $this->timezone_offset_seconds;
            $User->save();
        }

        return true;
    }

    /**
     * @param \common\models\Users $User
     * @return bool
     */
    public function setStaticTimeZone($User)
    {
        //var_dump($this->timezone_offset_seconds); exit;
        Yii::$app->session->set('UserTimeZoneOffset', $this->timezone_offset_seconds);
        $User->static_timezone = $this->timezone_offset_seconds;
        $User->save();
        return true;
    }
}
