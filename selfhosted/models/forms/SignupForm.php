<?php
namespace selfhosted\models\forms;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\Preferences;
use common\models\SelfHostUsers;
use common\models\MailTemplatesStatic;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $shu_company;
    public $shu_name;
    public $shu_email;
    public $shu_support_status;
    public $shu_brand_status;
    public $password;
    public $password_repeat;
    //public $acceptRules;
    public $reCaptchaSignup1;
    public $rememberMe = true;
    public $promo_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['shu_company', 'shu_name', 'shu_email', 'password', 'password_repeat'], 'required'],
            [['shu_name', 'shu_email', 'promo_code'], 'filter', 'filter' => 'trim'],
            [['shu_company', 'shu_name'], 'string', 'min' => 5, 'max' => 100],
            [['shu_email'], 'email'],
            [['shu_email'], 'unique', 'targetClass' => SelfHostUsers::className(), 'message' => Yii::t('forms/login-signup-form', 'For this E-Mail already registered some self-hosted account.')],
            ['promo_code', 'string', 'min' => 3, 'max' => 30],
            ['promo_code', 'match', 'pattern' => '/^[a-zA-Z0-9]{3,30}$/'],
            [['password'], 'string', 'min' => 6],
            [['password'], 'match','pattern' => Users::PASSWORD_PATTERN, 'message' => Yii::t('forms/login-signup-form', 'password_pattern')],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password'],
            [['shu_support_status', 'shu_brand_status'], 'integer', 'min' => 0, 'max' => 1],
            //['acceptRules', 'required', 'requiredValue' => 1, 'message' => Yii::t('forms/login-signup-form', 'accept_rules')],
            //[['reCaptchaSignup1'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SignupForm1).'],
        ];

        if (!Yii::$app->request->isAjax) {
            $cnt = Yii::$app->cache->get(Yii::$app->params['RegisterCacheKey']);
            if (!$cnt) {
                $cnt = 1;
                Yii::$app->cache->set(Yii::$app->params['RegisterCacheKey'], $cnt);
            }
            if ($cnt > Preferences::getValueByKey('RegisterCountNoCaptcha', 1, 'int')) {
                $rules[] = [['reCaptchaSignup1'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SignupForm1).'];
            }
        }

        return $rules;
    }

    /**
     * attribute for input fields.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'shu_company'     => Yii::t('forms/login-signup-form', 'user_company'),
            'shu_name'        => Yii::t('forms/login-signup-form', 'user_name'),
            'shu_email'       => Yii::t('forms/login-signup-form', 'user_email'),
            'password'        => Yii::t('forms/login-signup-form', 'password'),
            'password_repeat' => Yii::t('forms/login-signup-form', 'password_repeat'),
            'reCaptchaSignup1'   => Yii::t('forms/login-signup-form', 'reCaptcha'),
            'shu_support_status' => Yii::t('forms/login-signup-form', 'shu_support_status'),
            'shu_brand_status'   => Yii::t('forms/login-signup-form', 'shu_brand_status'),
            'promo_code'       => Yii::t('forms/login-signup-form', 'promo_code'),
        ];
    }

    public function login()
    {
        //var_dump($user->getAuthKey());
        //var_dump(Yii::$app->request->cookies->getValue('_identity'));
    }

    /**
     * Signs user up.
     *
     * @return Users|null the saved model or null if saving fails
     */
    public function signup()
    {
        $user              = new SelfHostUsers();
        $user->shu_company = $this->shu_company;
        $user->shu_name    = $this->shu_name;
        $user->shu_email   = $this->shu_email;
        $user->shu_status  = SelfHostUsers::STATUS_ACTIVE;
        $user->shu_role    = SelfHostUsers::ROLE_ROOT;
        $user->shu_support_status = ($this->shu_support_status) ? SelfHostUsers::ENABLED : SelfHostUsers::DISABLED;
        $user->shu_brand_status   = ($this->shu_brand_status) ? SelfHostUsers::ENABLED : SelfHostUsers::DISABLED;
        $user->license_count_available = SelfHostUsers::MAX_AVAILABLE_LICENSES - 1;
        $user->license_count_used = 1;
        $user->shu_promo_code = $this->promo_code;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generatePasswordResetToken();

        if ($user->save()) {

//            MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newShuRegister, $user->shu_email, [
//                'user_name'  => $user->shu_name,
//                'user_email' => $user->shu_email,
//                'user_key'   => $user->shu_user_hash,
//                'confirm_registration_link' => Yii::$app->urlManager->createAbsoluteUrl(['user/confirm-registration', 'token' => $user->password_reset_token]),
//            ]);

            if ($user->shu_support_status || $user->shu_brand_status) {
                // емейл со ссылкой не отправляем и ставим флеш-мессадж о том что с ним скоро свяжутся
                if ($user->shu_support_status) {
                    $user->shu_support_requested = SelfHostUsers::YES;
                }
                if ($user->shu_brand_status) {
                    $user->shu_brand_requested = SelfHostUsers::YES;
                }

                $user->requestSupportOrBrand();

            } else {
                // отправляем емейл со сслкой на скачивание архива и ставим об этом флеш-мессадж

                $cache_key = 'shu-email-with-link-sent-' . $user->shu_id;
                Yii::$app->cache->set($cache_key, time(), Yii::$app->params['timeout_resend_confirm']);
                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newShuDownload, $user->shu_email, [
                    'user_name' => $user->shu_name,
                    'user_email' => $user->shu_email,
                    'user_key'   => $user->shu_user_hash,
                    'download_shu_link' => Yii::$app->urlManager->createAbsoluteUrl(['/download_shu_link']),
                ]);
            }

            return [
                'user' => $user,
                'free' => (int) (!$user->shu_support_status && !$user->shu_brand_status),
            ];
        }

        return null;
    }
}
