<?php
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use backend\models\Admins;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $new_password;
    public $repeat_password;

    /**
     * @var \backend\models\Admins
     */
    private $_user;


    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            //throw new Exception('Password reset token cannot be blank.');
            throw new InvalidParamException('Password reset token cannot be blank.');
        }
        $this->_user = Admins::findByPasswordResetToken($token);
        //var_dump($this->_user); exit;
        if (!$this->_user) {
            //throw new Exception('Wrong password reset token.');
            throw new InvalidParamException('Wrong password reset token.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'new_password'    => 'New password',
            'repeat_password' => 'Repeat password',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['new_password', 'repeat_password'], 'required'],
            ['new_password', 'string', 'min' => 6],
            ['repeat_password', 'compare', 'compareAttribute' => 'new_password'],
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->new_password);
        $user->removePasswordResetToken();

        if ($user->save(false)) {

            return true;
        } else {
            return false;
        }

        //return $user->save(false);
    }
}
