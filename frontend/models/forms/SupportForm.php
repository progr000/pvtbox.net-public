<?php

namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\MailTemplatesStatic;
use common\models\Preferences;
use common\models\Tikets;
use common\models\TiketsMessages;
use common\models\MessagesStore;
use common\models\Users;

/**
 * SupportForm is the model behind the contact form.
 */
class SupportForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $reCaptchaSupport;

    const SUBJECT_CHOOSE    = 'CHOOSE';
    const SUBJECT_TECHNICAL = 'TECHNICAL';
    const SUBJECT_LICENSES  = 'LICENSES';
    const SUBJECT_FEEDBACK  = 'FEEDBACK';
    const SUBJECT_OTHER     = 'OTHER';

    /**
     * @inheritdoc
     */
    public function rules()
    {

        $rules = [
            //[['name', 'email', 'subject', 'body'], 'required'],
            //['subject', 'string', 'max' => 255],
            ['subject', 'in', 'range' => [
                self::SUBJECT_TECHNICAL,
                self::SUBJECT_LICENSES,
                self::SUBJECT_FEEDBACK,
                self::SUBJECT_OTHER,
            ]],
            ['body', 'safe'],
        ];

        if (Yii::$app->user->isGuest) {
            $rules[] = [['name', 'email', 'subject', 'body'], 'required'];
            $rules[] = ['email', 'email'];
            $rules[] = ['name', 'string', 'max' => 50];

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

            //$rules[] = [['reCaptchaSupport'], \himiklab\yii2\recaptcha\ReCaptchaValidator::className(), 'secret' => Preferences::getValueByKey('reCaptchaSecretKey'), 'uncheckedMessage' => 'Please confirm that you are not a bot (SupportForm).'];
        } else {
            $rules[] = [['subject', 'body'], 'required'];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'             => Yii::t('forms/support-form', 'Your_name'),
            'email'            => Yii::t('forms/support-form', 'Your_email'),
            'subject'          => Yii::t('forms/support-form', 'Subject'),
            'body'             => Yii::t('forms/support-form', 'Your_question'),
            'reCaptchaSupport' => Yii::t('forms/support-form', 'reCaptcha'),
        ];
    }

    /**
     * @return array
     */
    public static function subjectLabels()
    {
        return [
            //self::SUBJECT_CHOOSE    => Yii::t('forms/support-form', 'SUBJECT_CHOOSE'),
            self::SUBJECT_TECHNICAL => Yii::t('forms/support-form', 'SUBJECT_TECHNICAL'),
            self::SUBJECT_LICENSES  => Yii::t('forms/support-form', 'SUBJECT_LICENSES'),
            self::SUBJECT_FEEDBACK  => Yii::t('forms/support-form', 'SUBJECT_FEEDBACK'),
            self::SUBJECT_OTHER     => Yii::t('forms/support-form', 'SUBJECT_OTHER'),
        ];
    }

    /**
     * @param string $subject_code
     * @return string mixed
     */
    public static function getSubjectLabel($subject_code)
    {
        $labels = self::subjectLabels();
        if (isset($labels[$subject_code])) {
            return $labels[$subject_code];
        }

        return $subject_code;
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @return boolean whether the email was sent
     */
    public function sendEmail()
    {
        /** @var $tk \common\models\Tikets */
        /** @var $user \common\models\Users */
        /*
        if (Yii::$app->user->isGuest) {
            $tk = Tikets::findOne(['user_id' => 0, 'tiket_email' => $this->email]);
            if (!$tk) { $tk  = new Tikets(); }
            $tk->user_id     = 0;
            $tk->tiket_email = $this->email;
            $tk->tiket_name  = $this->name;
        } else {
            $user = Yii::$app->user->identity;
            //var_dump($user->user_email); exit;
            $tk  = new Tikets();
            $tk->user_id     = $user->getId();
            $tk->tiket_email = $user->user_email;
            $tk->tiket_name  = $user->user_name;

            $this->email = $user->user_email;
            $this->name  = $user->user_name;
        }

        $tk->tiket_theme     = $this->subject;
        $tk->tiket_count_new_admin += 1;
        $tk->tiket_count_new_user   = 0;
        if ($tk->save()) {
            $tkm = new TiketsMessages();
            $tkm->tiket_id = $tk->tiket_id;
            $tkm->message_text = $this->body;
            $tkm->user_id = $tk->user_id;
            $tkm->save();
        }
        */

        $to = Preferences::getValueByKey("supportEmail_{$this->subject}", Preferences::getValueByKey('adminEmail'));
        if (Yii::$app->user->isGuest) {
            $User = null;
            $from_name = "{$this->name} <{$this->email}>";
            $reply_to_email = $this->email;
            $reply_to_name  = $this->name;
        } else {
            $User = Users::findIdentity(Yii::$app->user->getId());
            $from_name = "{$User->user_name} <{$User->user_email}>";
            $reply_to_email = $User->user_email;
            $reply_to_name  = $User->user_name;
        }

        $subject = self::getSubjectLabel($this->subject) . " from {$from_name}";

        $body = "Message from support form\n\n" .
            "Name: {$from_name}\n" .
            "Email: {$reply_to_email}\n" .
            "Subject: {$subject}\n" .
            "Message: \n\n" . $this->body . "\n";

        $ms = new MessagesStore();
        $ms->ms_data = $body;
        $ms->ms_type = MessagesStore::TYPE_SUPPORT;
        $ms->user_id = ($User) ? $User->user_id : null;
        $ms->save();

        return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
            'from_name'      => $from_name,
            //'from_email'     => 'support@pvtbox.net',
            'reply_to_email' => $reply_to_email,
            'reply_to_name'  => $reply_to_name,
            'subject'        => $subject,
            'body'           => $this->body,
            'to_name'        => 'Support',
            'UserObject'     => $User,
        ]);
    }
}
