<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_actions_log}}".
 *
 * @property int $record_id ID
 * @property string $action_created Date
 * @property string $action_url Url of page, where action is coming
 * @property string $action_type Type of action (post|get)
 * @property string $action_raw_data raw data of action
 * @property int $user_id UserID
 * @property string $site_url Url of page, where action is coming
 * @property string $site_absolute_url Url of page, where action is coming
 *
 * @property Users $user
 */
class UserActionsLog extends \yii\db\ActiveRecord
{
    private static $excluded_actions = [
        'site/index',
        //'user/files',
        'user/register-alert-data',
        'user/count-new-events',
        'user/count-new-notifications',
        'user/set-reports-as-read',
        'user/alert-dialogs',
        'user/get-user-license',
        'user/get-captcha',

    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_actions_log}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'action_created',
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
            [['action_url', 'site_url', 'site_absolute_url', 'action_raw_data'], 'required'],
            [['action_created'], 'safe'],
            [['action_raw_data'], 'string'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['action_url', 'site_url', 'site_absolute_url'], 'string', 'max' => 255],
            [['action_type'], 'string', 'max' => 32],
            [['user_id'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'record_id' => 'ID',
            'action_created' => 'Date',
            'action_url' => 'Url of page, where action is coming',
            'action_type' => 'Type of action (post|get)',
            'action_raw_data' => 'raw data of action',
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
     * @param $user_id
     */
    public static function saveUserActionData($user_id)
    {
        if (isset(Yii::$app->controller->action->id, Yii::$app->controller->id))
        {
            $c_a = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
            if (in_array($c_a, self::$excluded_actions)) {
                return;
            }
        }

        $createLogOfUserActions = Preferences::getValueByKey('createLogOfUserActions', 0, 'integer');
        $method = mb_strtolower(Yii::$app->request->method);

        $log = false;
        if ($createLogOfUserActions == 1) {
            $log = true;
        } elseif ($createLogOfUserActions == 2 && $method == 'post') {
            $log = true;
        } else if ($createLogOfUserActions == 3 && $method == 'get') {
            $log = true;
        }

        if ($log) {
            $action = new UserActionsLog();
            $action->user_id = $user_id;
            //var_dump(Yii::$app->controller->id);
            //var_dump(Yii::$app->controller->action->id);
            //var_dump(Yii::$app->controller->action->actionMethod);
            $action->site_url = Yii::$app->request->getUrl();
            $action->site_absolute_url = Yii::$app->request->getAbsoluteUrl();
            $action_url = "";
            $action_url .= isset(Yii::$app->controller->id) ? Yii::$app->controller->id : "unknown_controller";
            $action_url .= "/";
            $action_url .= isset(Yii::$app->controller->action->id) ? Yii::$app->controller->action->id : "unknown_action";
            $action_url .= isset(Yii::$app->controller->action->actionMethod) ? " (" . Yii::$app->controller->action->actionMethod . ")" : "";
            $action->action_url = $action_url;
            $action->action_type = $method;
            $action->action_raw_data =
                "_POST: \n" . var_export($_POST, true) . "\n\n" .
                "_GET: \n" . var_export($_GET, true) . "\n\n" .
                "_SERVER: \n" . var_export($_SERVER, true) . "\n\n";
            $action->save();
            //var_dump($action->save());
            //var_dump($action->getErrors());
            //var_dump($action);
            //exit;
        }
    }
}
