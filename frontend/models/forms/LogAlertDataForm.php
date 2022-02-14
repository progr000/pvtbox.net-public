<?php
namespace frontend\models\forms;

use common\models\UserAlertsLog;
use Yii;
use yii\base\Model;
use common\helpers\FileSys;

/**
 * Transfer form
 */
class LogAlertDataForm extends Model
{
    public $screen;
    public $alert_data;
    public $url;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'alert_data'], 'required'],
            [['screen'], 'string'],
            [['alert_data'], 'checkIsArray'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function checkIsArray($attribute, $params) {
        if (!is_array($this->$attribute)){
            $this->addError('alert_data','alert_data is not array!');
        }

        $arr = $this->$attribute;

        foreach ($arr as $v) {
            if (!isset($v['message'], $v['closeButton'], $v['ttl'], $v['viewType'], $v['type'], $v['action'])) {
                $this->addError('alert_data', 'alert_data is not a valid array!');
                return false;
            }

            if (!in_array($v['closeButton'], [UserAlertsLog::CLOSE_DISABLED, UserAlertsLog::CLOSE_ENABLED])) {
                $this->addError('alert_data', 'alert_data.closeButton is not a valid!');
                return false;
            }

            if (!in_array($v['viewType'], [UserAlertsLog::VIEW_FLASH, UserAlertsLog::VIEW_SNACK])) {
                $this->addError('alert_data', 'alert_data.viewType is not a valid!');
                return false;
            }

            if (!in_array($v['type'], [UserAlertsLog::TYPE_SUCCESS, UserAlertsLog::TYPE_DANGER, UserAlertsLog::TYPE_ERROR, UserAlertsLog::TYPE_UNKNOWN])) {
                $this->addError('alert_data', 'alert_data.type is not a valid!');
                return false;
            }
        }
    }

    /**
     * @param \common\models\Users $User
     * @return string
     */
    public function saveAlertData($User)
    {
        $image = null;
        //var_dump($this->screen); exit;
        if ($this->screen) {
            $image_parts = explode(";base64,", $this->screen);
            //var_dump($image_parts); exit;
            if (isset($image_parts[1])) {
                $image = base64_decode($image_parts[1]);
                //$path = Yii::getAlias('@backend_uploads_fs') . DIRECTORY_SEPARATOR . $User->user_id . DIRECTORY_SEPARATOR;
                //FileSys::mkdir($path, 0777, true);
                //var_dump($path); exit;
                //$filename = $path . "screenshot_" . uniqid() . '.png';
                //file_put_contents($filename, $image);
            } else {
                $image = "Error: ". $this->screen;
            }
        }

        $ret = true;
        $info = 'ok';
        foreach ($this->alert_data as $v) {
            $al = new UserAlertsLog();
            $al->user_id = $User->user_id;
            $al->alert_url = $this->url;
            $al->alert_screen = $image;

            $al->alert_message = $v['message'];
            $al->alert_ttl = intval($v['ttl']);
            $al->alert_close_button = intval($v['closeButton']);
            $al->alert_type = $v['type'];
            $al->alert_view_type = $v['viewType'];
            $al->alert_action = mb_substr($v['action'], 0, 50);
            if (!$al->save()) {
                $ret  = false;
                $info = $al->getErrors();
            }
        }

        return [
            'status' => $ret,
            'info'   => $info,
        ];
    }

}
