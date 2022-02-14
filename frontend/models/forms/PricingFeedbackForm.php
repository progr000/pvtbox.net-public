<?php

namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\MailTemplatesStatic;
use common\models\Preferences;
use common\models\Users;
use common\models\MessagesStore;

/**
 * SupportForm is the model behind the contact form.
 */
class PricingFeedbackForm extends Model
{
    public $name;
    public $organization;
    public $email;
    public $phone;
    public $count_users;
    public $body;
    public $reCaptchaSupport;

    const PHONE_PATTERN = "/^(([0-9]{2}|\+[0-9]{2})[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/";

    /**
     * @inheritdoc
     */
    public function rules()
    {

        $rules = [
            [['name', 'organization', 'email', 'phone', 'count_users', 'body'], 'required'],
            [['name', 'organization'], 'string', 'max' => 50],
            [['email'], 'email'],
            [['phone'], 'match','pattern' => self::PHONE_PATTERN, 'message' => Yii::t('forms/pricing-feedback-form', 'Wrong phone format')],
            [['count_users'], 'integer', 'min' => 1],
            [['body'], 'safe'],
        ];

        if (Yii::$app->user->isGuest) {

            if (!Yii::$app->request->isAjax) {
                $reCaptchaSecretKey = Preferences::getValueByKey('reCaptchaSecretKey');
                $cnt = Yii::$app->cache->get(Yii::$app->params['ContactCacheKey']);
                if (!$cnt) {
                    $cnt = 1;
                    Yii::$app->cache->set(Yii::$app->params['ContactCacheKey'], $cnt);
                }
                if (!$reCaptchaSecretKey) {
                    $cnt = 1;
                }
                if ($cnt > Preferences::getValueByKey('ContactCountNoCaptcha', 1, 'int')) {
                    $rules[] = [['reCaptchaSupport'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SupportForm).'];
                }
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
            'name'             => Yii::t('forms/pricing-feedback-form', 'Your name'),
            'organization'     => Yii::t('forms/pricing-feedback-form', 'Organization'),
            'email'            => Yii::t('forms/pricing-feedback-form', 'Business Email'),
            'phone'            => Yii::t('forms/pricing-feedback-form', 'Phone number'),
            'count_users'      => Yii::t('forms/pricing-feedback-form', 'Number of users'),
            'body'             => Yii::t('forms/pricing-feedback-form', 'Please specify any additional information'),
            'reCaptchaSupport' => Yii::t('forms/pricing-feedback-form', 'reCaptcha'),
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @return boolean whether the email was sent
     */
    public function sendEmail()
    {

        if (Yii::$app->user->isGuest) {
            $User = null;
        } else {
            $User = Users::findIdentity(Yii::$app->user->getId());
        }
        $to = Preferences::getValueByKey("supportEmail_OTHER", Preferences::getValueByKey('adminEmail'));
        $from_name = "{$this->name} <{$this->email}>";
        $reply_to_email = $this->email;
        $reply_to_name  = $this->name;

        $body = "Message from pricing feedback form\n\n" .
                "Name: {$this->name}\n" .
                "Organization: {$this->organization}\n" .
                "Email: {$this->email}\n" .
                "Phone: {$this->phone}\n" .
                "Number of users: {$this->count_users}\n" .
                "Additional information: \n\n" . $this->body . "\n";

        $ms = new MessagesStore();
        $ms->ms_data = $body;
        $ms->ms_type = MessagesStore::TYPE_PRICING;
        $ms->user_id = ($User) ? $User->user_id : null;
        $ms->save();

        return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
            'from_name'      => $from_name,
            //'from_email'     => 'support@pvtbox.net',
            'reply_to_email' => $reply_to_email,
            'reply_to_name'  => $reply_to_name,
            'subject'        => 'From pricing feedback form',
            'body'           => $body,
            'to_name'        => 'Support',
            'UserObject'     => $User,
        ]);
    }
}
