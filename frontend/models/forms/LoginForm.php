<?php
namespace frontend\models\forms;

use common\models\BadLogins;
use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\Preferences;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $user_name;
    public $user_email;
    public $password;
    public $reCaptchaLogin;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['user_email', 'password'], 'required'],
            ['user_email', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'], /* password is validated by validatePassword() */
            //[['reCaptchaLogin'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (LoginForm).'],
        ];


        if (!Yii::$app->request->isAjax) {
            $reCaptchaSecretKey = Preferences::getValueByKey('reCaptchaSecretKey');
            $cnt = Yii::$app->cache->get(Yii::$app->params['LoginCacheKey']);
            if (!$cnt) {
                $cnt = 1;
                Yii::$app->cache->set(Yii::$app->params['LoginCacheKey'], $cnt);
            }
            if (!$reCaptchaSecretKey) {
                $cnt = 1;
            }
            if ($cnt > Preferences::getValueByKey('LoginCountNoCaptcha', 1, 'int')) {
                $rules[] = [['reCaptchaLogin'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (LoginForm).'];
            }
        }


        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'rememberMe'     => Yii::t('forms/login-signup-form', 'rememberMe'),
            'user_name'      => Yii::t('forms/login-signup-form', 'user_name'),
            'user_email'     => Yii::t('forms/login-signup-form', 'user_email'),
            'password'       => Yii::t('forms/login-signup-form', 'password'),
            'reCaptchaLogin' => Yii::t('forms/login-signup-form', 'reCaptcha'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('forms/login-signup-form', 'Invalid_Email_or_Password'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        //if ($this->validate()) {
            $user = $this->getUser();
            $ip = null;
            $ip = Yii::$app->request->getUserIP();
            if (!$ip) { $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null; }
            if (!$ip) { $ip = '127.0.0.1'; }
            $user->user_last_ip = $ip;

            if ($user->save()) {
                /* если успешно авторизованы, то удалить из списка */
                BadLogins::removeIpFromList($ip, BadLogins::TYPE_LOCK_LOGIN);
                BadLogins::removeIpFromList($ip, BadLogins::TYPE_LOCK_RESET);

                return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 365 : 0);
            }
        //}
        //var_dump($this->getErrors()); exit;
        return false;
    }

    /**
     * Finds user by [[user_email]]
     *
     * @return Users|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Users::findByEmail($this->user_email);
        }

        return $this->_user;
    }
}
