<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
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
class SignupForm extends Model
{
    public $user_name;
    public $user_email;
    public $password;
    public $password_repeat;
    public $acceptRules;
    public $reCaptchaSignup1;
    public $promo_code;

    public $email_read_only = false;

    /*
     * вариант решения регистронезависимой проверки емейла при регистрации если не хочется использовать библиотеку постгре citext
     * но тогда придется такую функцию и правило добавить во все формы регистрации и в апи тоже
     * с вариантом citext мы получаем более гибкое и сквозное решени
    public function getUseremailLowercase()
    {
        return mb_strtolower($this->user_email);
        // add tu rules^
        // ['user_email', 'unique', 'targetAttribute' => ['useremailLowercase' => 'lower(user_email)'], 'targetClass' => '\common\models\Users', 'message' => 'This E-Mail address has already been taken.'],
    }
    */

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $colleague_id = intval(Yii::$app->request->get('colleague_id'));
        if ($colleague_id) {
            $UserColleague = UserColleagues::findOne(['colleague_id' => $colleague_id]);
            if ($UserColleague) {
                $this->user_email = $UserColleague->colleague_email;
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
            [[/*'user_name', */'user_email', 'password', 'password_repeat', 'acceptRules'], 'required'],
            [['user_name', 'user_email', 'promo_code'], 'filter', 'filter' => 'trim'],
            ['user_name', 'string', 'min' => 2, 'max' => 50],
            ['user_email', 'email'],
            ['user_email', 'unique', 'targetClass' => '\common\models\Users', 'message' => Yii::t('forms/login-signup-form', 'email_taken')],
            ['promo_code', 'string', 'min' => 3, 'max' => 30],
            ['promo_code', 'match', 'pattern' => '/^[a-zA-Z0-9]{3,30}$/'],
            ['password', 'string', 'min' => 6],
            ['password', 'match','pattern' => Users::PASSWORD_PATTERN, 'message' => Yii::t('forms/login-signup-form', 'password_pattern')],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            ['acceptRules', 'required', 'requiredValue' => 1, 'message' => Yii::t('forms/login-signup-form', 'accept_rules')],
            //[['reCaptchaSignup1'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SignupForm1).'],
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
            'rememberMe'       => Yii::t('forms/login-signup-form', 'rememberMe'),
            'user_name'        => Yii::t('forms/login-signup-form', 'user_name'),
            'user_email'       => Yii::t('forms/login-signup-form', 'user_email'),
            'password'         => Yii::t('forms/login-signup-form', 'password'),
            'password_repeat'  => Yii::t('forms/login-signup-form', 'password_repeat'),
            'reCaptchaSignup1' => Yii::t('forms/login-signup-form', 'reCaptcha'),
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
        $user->user_name       = $this->user_name
            ? $this->user_name
            : Functions::getNameFromEmail($this->user_email);
        $user->user_email      = $this->user_email;
        $user->license_type    = Licenses::TYPE_FREE_TRIAL;
        $user->license_expire  = date(SQL_DATE_FORMAT, time() + Licenses::getCountDaysTrialLicense() * 86400);
        $user->user_last_ip    = Yii::$app->request->getUserIP();
        $user->user_ref_id     = Yii::$app->request->cookies->getValue('ref', null);
        $user->user_promo_code = $this->promo_code;
        if (Yii::$app->params['self_hosted']) {
            $user->user_status = Users::STATUS_CONFIRMED;
        }
        $user->setPassword($this->password);
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
