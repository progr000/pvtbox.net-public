<?php

namespace selfhosted\models\forms;

use Yii;
use yii\base\Model;
use common\models\MailTemplatesStatic;
use common\models\Preferences;
use common\models\SelfHostUsers;

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
    const SUBJECT_OTHER     = 'OTHER';

    /**
     * @inheritdoc
     */
    public function rules()
    {

        $rules = [
            //[['name', 'email', 'subject', 'body'], 'required'],
            //['subject', 'string', 'max' => 255],
            ['subject', 'in', 'range' => [self::SUBJECT_TECHNICAL, self::SUBJECT_LICENSES, self::SUBJECT_OTHER]],
            ['body', 'safe'],
        ];

        if (Yii::$app->user->isGuest) {
            $rules[] = [['name', 'email', 'subject', 'body'], 'required'];
            $rules[] = ['email', 'email'];
            $rules[] = ['name', 'string', 'max' => 50];

            if (!Yii::$app->request->isAjax) {
                $cnt = Yii::$app->cache->get(Yii::$app->params['ContactCacheKey']);
                if (!$cnt) {
                    $cnt = 1;
                    Yii::$app->cache->set(Yii::$app->params['ContactCacheKey'], $cnt);
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
        /** @var $user \common\models\SelfHostUsers */

        $to = Preferences::getValueByKey("supportEmail_{$this->subject}", Preferences::getValueByKey('adminEmail'));
        if (Yii::$app->user->isGuest) {
            $User = null;
            $from_name = "{$this->name} <{$this->email}>";
            $reply_to_email = $this->email;
            $reply_to_name  = $this->name;
        } else {
            $User = SelfHostUsers::findIdentity(Yii::$app->user->getId());
            $from_name = "{$User->shu_name} <{$User->shu_email}>";
            $reply_to_email = $User->shu_email;
            $reply_to_name  = $User->shu_name;
        }

        $subject = self::getSubjectLabel($this->subject) . " from {$from_name}";

        return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
            'from_name'      => $from_name,
            //'from_email'     => 'support@pvtbox.net',
            'reply_to_email' => $reply_to_email,
            'reply_to_name'  => $reply_to_name,
            'subject'        => $subject,
            'body'           => $this->body,
            'to_name'        => 'Support',
        ]);
    }
}
