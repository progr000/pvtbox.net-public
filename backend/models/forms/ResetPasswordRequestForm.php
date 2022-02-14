<?php
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use backend\models\Admins;
use common\models\MailTemplatesStatic;
use common\models\Preferences;

/**
 * Password reset request form
 */
class ResetPasswordRequestForm extends Model
{
    public $admin_email;
    public $reCaptcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['admin_email', 'filter', 'filter' => 'trim'],
            ['admin_email', 'required'],
            ['admin_email', 'email'],
            ['admin_email', 'exist',
                'targetClass' => '\backend\models\Admins',
                'filter' => ['admin_status' => [Admins::STATUS_ACTIVE]],
                'message' => 'There is no user with such email.'
            ],
            //[['reCaptcha'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'admin_email' => 'E-Mail',
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        $user = Admins::findOne([
            'admin_status' => [Admins::STATUS_ACTIVE],
            'admin_email' => $this->admin_email,
        ]);

        if ($user) {
            if (!Admins::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save()) {
                return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_PasswordReset, $user->admin_email, ['AdminObject' => $user]);
            }
        }

        return false;
    }
}
