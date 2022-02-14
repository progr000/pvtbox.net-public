<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\MailTemplatesStatic;
use frontend\models\NodeApi;

/**
 * Profile form
 */
class ChangePasswordForm extends Model
{
    public $old_password;
    public $new_password;
    public $repeat_password;
    public $token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password', 'repeat_password', 'token'], 'required'],
            [['old_password'], 'findPasswords'],
            [['new_password'], 'string', 'min' => 6],
            [['repeat_password'], 'compare', 'compareAttribute' => 'new_password'],
        ];
    }

    /**
     * @param string $attribute
     * @param array $params
     */
    public function findPasswords($attribute, $params)
    {
        $user = Users::findIdentity(Yii::$app->user->id);

        if (!$user->validatePassword($this->old_password)) {
            $this->addError($attribute, Yii::t('forms/change-password-form', 'Old_password_incorrect'));
            //return false;
        }

        if ($user->validatePassword($this->new_password)) {
            $this->addError('new_password', Yii::t('forms/change-password-form', 'New_password_is_same_as_old'));
            //return false;
        }
    }

    /**
     * attribute for input fields.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'token' => '',
            'old_password'    => Yii::t('forms/change-password-form', 'Current_Password'),
            'new_password'    => Yii::t('forms/change-password-form', 'New_Password'),
            'repeat_password' => Yii::t('forms/change-password-form', 'Retype_Password'),
        ];
    }

    /**
     * Signs user up.
     *
     * @return Users|null the saved model or null if saving fails
     */
    public function changePasswordStep2()
    {
        if ($this->validate()) {
            $user = Users::findByPasswordResetToken($this->token);
            if ($user) {
                $user->setPassword($this->new_password);
                $user->removePasswordResetToken();

                if ($user->save()) {

                    // тут сгенерировать новые ремот-акшены=credentials для нод credentials
                    NodeApi::sendCredentialsForUserNodes($user);

                    $this->old_password = "";
                    $this->new_password = "";
                    $this->repeat_password = "";
                    return $user;
                }
            }
        }
        //var_dump($this->getErrors());
        return null;
    }

    /**
     * @param string $token
     * @return null|static
     */
    public function findChangeToken($token)
    {
        return Users::findByPasswordResetToken($token);
    }

    /**
     * @return bool
     */
    public function changePasswordStep1()
    {
        $user = Users::findIdentity(Yii::$app->user->id);

        if ($user) {
            if (!Users::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save()) {
                return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_PasswordChange, $user->user_email, ['UserObject' => $user]);
            }
        }

        return false;
    }

}
