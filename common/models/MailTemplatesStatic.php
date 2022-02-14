<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * This is the model class for table "{{%mail_templates}}".
 *
 * @property string $template_id
 * @property string $template_key
 * @property string $template_lang
 * @property string $template_to_email
 * @property string $template_to_name
 * @property string $template_from_email
 * @property string $template_from_name
 * @property string $template_reply_to_email
 * @property string $template_reply_to_name
 * @property string $template_subject
 * @property string $template_body_html
 * @property string $template_body_text
 */
class MailTemplatesStatic extends Model
{
    public $template_id;
    public $template_key;
    public $template_lang;
    public $template_to_email;
    public $template_to_name;
    public $template_from_email;
    public $template_from_name;
    public $template_reply_to_email;
    public $template_reply_to_name;
    public $template_subject;
    public $template_body_html;
    public $template_body_text;

    const template_key_standardMailTpl      = 'standard-mail-template';
    const template_key_downloadMobile       = 'download-mobile';
    const template_key_newRegister          = 'new-register';
    const template_key_PasswordChange       = 'password-change';
    const template_key_PasswordReset        = 'password-reset';
    const template_key_SetupDevices         = 'setup-devices';
    const template_key_ShareSendToEmail     = 'share-send-to-email';
    const template_key_CollaborationInvite  = 'collaboration-invite';
    const template_key_CollaborationInclude = 'collaboration-include';
    const template_key_LicenseExpired       = 'license-expired';
    const template_key_LicenseExpireSoon    = 'license-expire-soon';
    const template_key_newShuRegister       = 'new-shu-register';
    const template_key_newShuDownload       = 'new-shu-download';
    const template_key_ConferenceInvite     = 'conference-invite';
    const template_key_GuestRoomLink        = 'guest-room-link';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_key', 'template_lang', 'template_to_email', 'template_from_email', 'template_subject', 'template_body_html', 'template_body_text'], 'required'],
            [['template_body_html', 'template_body_text'], 'string'],
            [['template_key', 'template_from_name'], 'string', 'max' => 50],
            [['template_lang'], 'string', 'max' => 3],
            [['template_from_name', 'template_to_name', 'template_reply_to_name'], 'string', 'max' => 50],
            [['template_from_email', 'template_to_email', 'template_reply_to_email'], 'email'],
            [['template_subject'], 'string', 'max' => 255],
        ];
    }

    /**
     * @param string $template_key
     */
    public function __construct($template_key, array $config=[])
    {
        parent::__construct($config);

        $this->template_lang       = Yii::$app->language;
        $this->template_key        = $template_key;
        $this->template_from_email = Yii::t('mail/' . $template_key, 'from_email');
        $this->template_from_name  = Yii::t('mail/' . $template_key, 'from_name');
        $this->template_subject    = Yii::t('mail/' . $template_key, 'subject');
        $this->template_body_html  = Yii::t('mail/' . $template_key, 'body_html');
        $this->template_body_text  = Yii::t('mail/' . $template_key, 'body_text');

        if (isset(Yii::$app->params['robot_email_from'])) {
            $this->template_from_email = Yii::$app->params['robot_email_from'];
        }

        if (isset(Yii::$app->params['robot_name_from'])) {
            $this->template_from_name = Yii::$app->params['robot_name_from'];
        }
    }

    /**
     * @param string $email_to
     * @param array $data search(key)->replace(value)-array
     * @param string|null $template_key
     * @return bool;
     */
    public function send($email_to, array $data, $template_key=null)
    {
        $this->template_to_email = $email_to;
        $this->template_to_name = mb_substr($this->template_to_email, 0, mb_strpos($this->template_to_email, '@'));

        if (isset($data['from_name'])) { $this->template_from_name = $data['from_name']; }
        if (isset($data['from_email'])) { $this->template_from_email = $data['from_email']; }
        if (isset($data['reply_to_email'])) {
            $this->template_reply_to_email = $data['reply_to_email'];
            $this->template_reply_to_name =  mb_substr($this->template_reply_to_email, 0, mb_strpos($this->template_reply_to_email, '@'));
        }
        if (isset($data['reply_to_name'])) {
            $this->template_reply_to_name = $data['reply_to_name'];
        }
        if (isset($data['subject'])) {
            $this->template_subject = $data['subject'];
        }

        if ($this->validate()) {

            Yii::$app->urlManager->setHostInfo(Yii::getAlias('@frontendWeb'));
            if (!isset($data['app_name'])) { $data['app_name'] = Yii::$app->name; }
            if (!isset($data['user_email'])) { $data['user_email'] = $this->template_to_email; }
            if (!isset($data['download_app_link'])) { $data['download_app_link'] = Yii::$app->urlManager->createAbsoluteUrl(['download']); }
            if (!isset($data['collaboration_include_link'])) { $data['collaboration_include_link'] = Yii::$app->urlManager->createAbsoluteUrl(['user/files']); }

            if (isset($data['UserObject']) && is_object($data['UserObject'])) {
                /** @var \common\models\Users $User */
                $User = &$data['UserObject'];
                if (!isset($data['user_name'])) { $data['user_name'] = $User->user_name; }
                if (!isset($data['confirm_registration_link'])) { $data['confirm_registration_link'] = Yii::$app->urlManager->createAbsoluteUrl(['user/confirm-registration', 'token' => $User->password_reset_token]); }
                if (!isset($data['change_password_link'])) { $data['change_password_link'] = Yii::$app->urlManager->createAbsoluteUrl(['user/change-password', 'token' => $User->password_reset_token]); }
                if (!isset($data['reset_password_link'])) { $data['reset_password_link'] = Yii::$app->urlManager->createAbsoluteUrl(['user/reset-change-password', 'token' => $User->password_reset_token]); }
            }

            if (isset($data['AdminObject']) && is_object($data['AdminObject'])) {
                /** @var \backend\models\Admins $Admin */
                $Admin = &$data['AdminObject'];
                if (!isset($data['reset_password_link'])) { $data['reset_password_link'] = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $Admin->password_reset_token]); }
            }

            if (isset($data['UserFileObject']) && is_object($data['UserFileObject'])) {
                /** @var \common\models\UserFiles $UserFile */
                $UserFile = &$data['UserFileObject'];
                if (!isset($data['share_link'])) { $data['share_link'] = UserFiles::getShareLink($UserFile->share_hash, $UserFile->is_folder); }
            }

            if (isset($data['UserColleagueObject']) && is_object($data['UserColleagueObject'])) {
                /** @var \common\models\UserColleagues $UserColleague */
                $UserColleague = &$data['UserColleagueObject'];
                if (!isset($data['collaboration_invite_link'])) { $data['collaboration_invite_link'] = Yii::$app->urlManager->createAbsoluteUrl(['user/accept-collaboration', 'colleague_id' => $UserColleague->colleague_id]); }
            }

            if (!isset($data['user_name'])) {
                $data['user_name'] = $this->template_to_name;
            } else {
                $this->template_to_name = $data['user_name'];
            }

            if (isset($data['to_name'])) {
                $this->template_to_name = $data['to_name'];
            }

            $search  = [];
            $replace = [];
            foreach ($data as $k=>$v) {
                if (!is_object($data[$k])) {
                    $search[] = "{" . $k . "}";
                    $replace[] = $v;
                }
            }

            $subject = str_replace($search, $replace, $this->template_subject);
            $bodyHtml = str_replace($search, $replace, $this->template_body_html);
            $bodyText = str_replace($search, $replace, $this->template_body_text);
            $from_name = str_replace($search, $replace, $this->template_from_name);
            $to_name = $this->template_to_name;

            try {

                /* compose mail */
                $mailer = Yii::$app->mailer;
                $newMail = $mailer
                    ->compose(['html' => 'universalTemplate-html', 'text' => 'universalTemplate-text'], [
                        'bodyHtml' => $bodyHtml,
                        'bodyText' => $bodyText,
                    ])
                    ->setTo([$this->template_to_email => $to_name])
                    ->setFrom([$this->template_from_email => $from_name])
                    ->setSubject($subject);
                    //->setHtmlBody(str_replace($search, $replace, $this->template_body_html))
                    //->setTextBody(str_replace($search, $replace, $this->template_body_text))

                if (!empty($this->template_reply_to_email)) {
                    $newMail->setReplyTo([$this->template_reply_to_email => $this->template_reply_to_name]);
                }

                /* attachment */
                if (!empty($data['attachment'])) {
                    if (file_exists($data['attachment'])) {
                        $newMail->attach($data['attachment']);
                    }
                }

                /* logger */
                $mailer2 = Yii::$app->mailer->getSwiftMailer();//->getTransport();
                $logger = new \Swift_Plugins_Loggers_ArrayLogger();
                $mailer2->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

                if ($newMail->send()) {
                    $ret = true;
                } else {
                    $ret = false;
                }


                $mailer_answer = $logger->dump();
                //var_dump($mailer_answer);
                $regexp_for_search_id = "/queued as ([a-z0-9]*)(?:$|\s)/siU";
                preg_match($regexp_for_search_id, $mailer_answer, $ma);
                //var_dump($ma);

                $mq                       = new Mailq();
                $mq->template_key         = $template_key;
                $mq->mailer_answer        = $mailer_answer;
                $mq->mailer_letter_id     = isset($ma[1]) ? $ma[1] : null;
                $mq->mailer_letter_status = isset($ma[1]) ? Mailq::STATUS_QUEUED : Mailq::STATUS_ERROR;
                $mq->mail_from            = $from_name . " <{$this->template_from_email}>";
                $mq->mail_to              = $to_name . " <{$this->template_to_email}>";
                $mq->mail_reply_to        = $this->template_reply_to_name . " <{$this->template_reply_to_email}>";
                $mq->mail_subject         = $subject;
                $mq->mail_body            = $bodyText;
                $mq->user_id              = isset($User) ? $User->user_id : null;
                $mq->node_id              = null;
                $mq->save();

                return $ret;
                /** https://stackoverflow.com/questions/5768389/swift-mailer-delivery-status */
                /** https://github.com/yiisoft/yii2/issues/7524 */
                /*
                $transport = new \Swift_SmtpTransport('pvtbox.net', 587, 'tls');
                $transport->setUsername('support@pvtbox.net');
                $transport->setPassword('supp%^&123');

                $mailer = new \Swift_Mailer($transport);

                $logger = new \Swift_Plugins_Loggers_EchoLogger();
                $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

                $message = (new \Swift_Message())
                    ->setTo([$this->template_to_email => $this->template_to_name])
                    ->setFrom([$this->template_from_email => str_replace($search, $replace, $this->template_from_name)])
                    ->setSubject(str_replace($search, $replace, $this->template_subject))
                    ->setBody(str_replace($search, $replace, $this->template_body_text), 'text');

                // Send the message
                $result = $mailer->send($message);
                echo $logger->dump();
                exit;
                */
            } catch (\Exception $e) {
            //} catch (\Swift_TransportException $e, Exc) {
                //echo "EXCEPTION\n\n";
                //var_dump($e->getMessage()); exit;

                $mq                       = new Mailq();
                $mq->template_key         = $template_key;
                $mq->mailer_answer        = "EXCEPTION \n\n" . $e->getMessage();
                $mq->mailer_letter_id     = isset($ma[1]) ? $ma[1] : null;
                $mq->mailer_letter_status = isset($ma[1]) ? Mailq::STATUS_QUEUED : Mailq::STATUS_ERROR;
                $mq->mail_from            = $from_name . " <{$this->template_from_email}>";
                $mq->mail_to              = $to_name . " <{$this->template_to_email}>";
                $mq->mail_reply_to        = $this->template_reply_to_name . " <{$this->template_reply_to_email}>";
                $mq->mail_subject         = $subject;
                $mq->mail_body            = $bodyText;
                $mq->user_id              = isset($User) ? $User->user_id : null;
                $mq->node_id              = null;
                $mq->save();

                return false;
            }
        } else {
            //var_dump($this->getErrors()); exit;
            return false;
        }
    }

    /**
     * @param string $template_key
     * @param string $email_to
     * @param array $data search(key)->replace(value)-array
     * @return bool;
     */
    public static function sendByKey($template_key, $email_to, array $data)
    {
        $tpl = new MailTemplatesStatic($template_key);
        return $tpl->send($email_to, $data, $template_key);
    }
}
