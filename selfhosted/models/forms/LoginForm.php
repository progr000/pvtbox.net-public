<?php
namespace selfhosted\models\forms;

use Yii;
use yii\base\Model;
use common\models\SelfHostUsers;
use common\models\Preferences;

/**
 * Login form
 * @property $_user \common\models\SelfHostUsers
 */
class LoginForm extends Model
{
    public $shu_name;
    public $shu_email;
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
            [['shu_email', 'password'], 'required'],
            ['shu_email', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];

        if (!Yii::$app->request->isAjax) {
            $cnt = Yii::$app->cache->get(Yii::$app->params['LoginCacheKey']);
            if (!$cnt) {
                $cnt = 1;
                Yii::$app->cache->set(Yii::$app->params['LoginCacheKey'], $cnt);
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
            $admin = $this->getUser();
            if (!$admin || !$admin->validatePassword($this->password)) {
                $this->addError($attribute, 'Wrong e-mail or password.');
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
        if ($this->validate()) {
            $this->getUser();
            if ($this->_user && in_array($this->_user->shu_status,[
                    SelfHostUsers::STATUS_ACTIVE,
                    SelfHostUsers::STATUS_CONFIRMED,
                    SelfHostUsers::STATUS_SH_LOCKED,
                ])) {
                return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 365 : 0);
            }
        }
        return false;
    }

    /**
     * Finds user by [[shu_email]]
     *
     * @return SelfHostUsers|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = SelfHostUsers::findByEmail($this->shu_email);
        }

        return $this->_user;
    }
}
