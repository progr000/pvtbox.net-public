<?php
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use backend\models\Admins;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $admin_name;
    public $admin_email;
    public $password;
    public $rememberMe = true;

    private $_admin;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // admin_name and password are both required
            //[['admin_name', 'password'], 'required'],
            [['admin_email', 'password'], 'required'],
            ['admin_email', 'email'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'rememberMe' => 'Remember me',
            'admin_email' => 'E-Mail',
            'password' => 'Password',
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
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[admin_email]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_admin === null) {
            $this->_admin = Admins::findByEmail($this->admin_email);
        }

        return $this->_admin;
    }
}
