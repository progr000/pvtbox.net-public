<?php
namespace selfhosted\models\forms;

use Yii;
use yii\base\Model;
use common\models\SelfHostUsers;
use common\models\MailTemplatesStatic;
use common\models\Preferences;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $user_email;
    public $reCaptchaResetPassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['user_email', 'filter', 'filter' => 'trim'],
            ['user_email', 'required'],
            ['user_email', 'email'],
            /*
            ['user_email', 'exist',
                'targetClass' => '\common\models\SelfHostUsers',
                //'filter' => ['user_status' => SelfHostUsers::STATUS_ACTIVE], //
                'filter' => ['user_status' => [SelfHostUsers::STATUS_ACTIVE, SelfHostUsers::STATUS_CONFIRMED]],
                'message' => 'There is no user with such email.'
            ],
            */
            //[['reCaptchaResetPassword'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (ResetPasswordForm).'],
        ];


        if (!Yii::$app->request->isAjax) {
            $cnt = Yii::$app->cache->get(Yii::$app->params['ResetPasswordCacheKey']);
            if (!$cnt) {
                $cnt = 1;
                Yii::$app->cache->set(Yii::$app->params['ResetPasswordCacheKey'], $cnt);
            }
            if ($cnt > Preferences::getValueByKey('ResetPasswordCountNoCaptcha', 1, 'int')) {
                $rules[] = [['reCaptchaResetPassword'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (ResetPasswordForm).'];
            }
        }


        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_email' => Yii::t('forms/reset-password-form', 'user_email'),
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user SelfHostUsers */
        $user = SelfHostUsers::findOne([
            //'shu_status' => [SelfHostUsers::STATUS_ACTIVE, SelfHostUsers::STATUS_CONFIRMED],
            'shu_email' => $this->user_email,
        ]);

        if ($user) {
            if (!SelfHostUsers::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save()) {
                return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_PasswordReset, $user->shu_email, [
                    'user_name' => $user->shu_name,
                    'user_email' => $user->shu_email,
                    'app_name' => Yii::getAlias('@selfHostedDomain'),
                    'reset_password_link' => Yii::$app->urlManager->createAbsoluteUrl(['site/reset-change-password', 'token' => $user->password_reset_token]),
                ]);
            }
        }

        return false;
    }
}
