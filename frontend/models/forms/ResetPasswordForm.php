<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\base\InvalidParamException;
use common\models\Users;
use frontend\models\NodeApi;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $new_password;
    public $repeat_password;

    /**
     * @var \common\models\Users
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
        $this->_user = Users::findByPasswordResetToken($token);
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
            'new_password'    => Yii::t('forms/reset-password-form', 'new_password'),//'Новый пароль',
            'repeat_password' => Yii::t('forms/reset-password-form', 'repeat_password'),//'Повтор пароля',
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

            // тут сгенерировать новые ремот-акшены=credentials для нод credentials
            NodeApi::sendCredentialsForUserNodes($user);

            return true;
        } else {
            return false;
        }

        //return $user->save(false);
    }
}
