<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\helpers\Functions;
use common\models\Users;
use common\models\UserNode;
use common\models\MailTemplatesStatic;
use common\models\Preferences;
use common\models\Sessions;
use common\models\Licenses;
use common\models\UserColleagues;
use frontend\models\NodeApi;

/**
 * Signup form
 */
class SignupForm2 extends Model
{
    public $user_name2;
    public $user_email2;
    public $password2;
    public $password_repeat2;
    public $acceptRules2;
    public $reCaptchaSignup2;
    public $promo_code;

    public $email_read_only = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $colleague_id = Yii::$app->request->get('colleague_id');
        if ($colleague_id) {
            $UserColleague = UserColleagues::findOne(['colleague_id' => $colleague_id]);
            if ($UserColleague) {
                $this->user_email2 = $UserColleague->colleague_email;
                $this->email_read_only = true;
            }
        }
        if (Yii::$app->params['self_hosted']) {
            $this->email_read_only = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [[/*'user_name2', */'user_email2', 'password2', 'password_repeat2', 'acceptRules2'], 'required'],
            [['user_name2', 'user_email2', 'promo_code'], 'filter', 'filter' => 'trim'],
            ['user_name2', 'string', 'min' => 2, 'max' => 50],
            ['user_email2', 'email'],
            ['user_email2', 'unique', 'targetClass' => '\common\models\Users', 'targetAttribute' => 'user_email', 'message' => Yii::t('forms/login-signup-form', 'email_taken')],
            ['promo_code', 'string', 'min' => 3, 'max' => 30],
            ['promo_code', 'match', 'pattern' => '/^[a-zA-Z0-9]{3,30}$/'],
            ['password2', 'string', 'min' => 6],
            ['password2', 'match','pattern' => Users::PASSWORD_PATTERN, 'message' => Yii::t('forms/login-signup-form', 'password_pattern')],
            ['password_repeat2', 'compare', 'compareAttribute' => 'password2'],
            ['acceptRules2', 'required', 'requiredValue' => 1, 'message' => Yii::t('forms/login-signup-form', 'accept_rules')],
            //[['reCaptchaSignup2'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SignupForm2).'],
        ];

        if (!Yii::$app->request->isAjax) {
            $reCaptchaSecretKey = Preferences::getValueByKey('reCaptchaSecretKey');
            $cnt = Yii::$app->cache->get(Yii::$app->params['RegisterCacheKey']);
            if (!$cnt) {
                $cnt = 1;
                Yii::$app->cache->set(Yii::$app->params['RegisterCacheKey'], $cnt);
            }
            if (!$reCaptchaSecretKey) {
                $cnt = 1;
            }
            if ($cnt > Preferences::getValueByKey('RegisterCountNoCaptcha', 1, 'int')) {
                $rules[] = [['reCaptchaSignup2'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SignupForm2).'];
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
            'rememberMe2'      => Yii::t('forms/login-signup-form', 'rememberMe'),
            'user_name2'       => Yii::t('forms/login-signup-form', 'user_name'),
            'user_email2'      => Yii::t('forms/login-signup-form', 'user_email'),
            'password2'        => Yii::t('forms/login-signup-form', 'password'),
            'password_repeat2' => Yii::t('forms/login-signup-form', 'password_repeat'),
            'reCaptchaSignup2' => Yii::t('forms/login-signup-form', 'reCaptcha'),
            'promo_code'       => Yii::t('forms/login-signup-form', 'promo_code'),
        ];
    }

    /**
     * Signs user up.
     *
     * @return Users|null the saved model or null if saving fails
     */
    public function signup()
    {
        //var_dump($this->validate()); var_dump($this->getErrors()); exit;
        $user                  = new Users();
        $user->user_name       = $this->user_name2
            ? $this->user_name2
            : Functions::getNameFromEmail($this->user_email2);
        $user->user_email      = $this->user_email2;
        $user->license_type    = Licenses::TYPE_FREE_TRIAL;
        $user->license_expire  = date(SQL_DATE_FORMAT, time() + Licenses::getCountDaysTrialLicense() * 86400);
        $user->user_last_ip    = Yii::$app->request->getUserIP();
        $user->user_ref_id     = Yii::$app->request->cookies->getValue('ref', null);
        $user->user_promo_code = $this->promo_code;
        if (Yii::$app->params['self_hosted']) {
            $user->user_status = Users::STATUS_CONFIRMED;
        }

        $user->setPassword($this->password2);
        $user->generateAuthKey();

        //$user->user_status = Users::STATUS_BLOCKED;
        $user->generatePasswordResetToken();

        if ($user->save()) {

            $UserNode = NodeApi::registerNodeFM($user);
            if ($UserNode) {
                $UserNode->node_online = UserNode::ONLINE_ON;
                $UserNode->node_useragent = Yii::$app->request->getUserAgent();
                $UserNode->node_osname = Functions::getOsExtendedByUserAgent($UserNode->node_useragent);
                $UserNode->node_ostype = Functions::getOsTypeByUserAgent($UserNode->node_useragent);
                $UserNode->save();
            }
            $session = new Sessions();
            $session->user_id = $user->user_id;
            $session->node_id = $UserNode->node_id;
            $session->sess_action = Sessions::ACTION_REGISTER;
            $session->save();

            MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newRegister, $user->user_email, ['UserObject' => $user]);
            return $user;
        }

        return null;
    }
}
