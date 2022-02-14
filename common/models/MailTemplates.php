<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%mail_templates}}".
 *
 * @property string $template_id
 * @property string $template_key
 * @property string $template_lang
 * @property string $template_from_email
 * @property string $template_from_name
 * @property string $template_subject
 * @property string $template_body_html
 * @property string $template_body_text
 */
class MailTemplates extends ActiveRecord
{
    const template_key_downloadMobile   = 'downloadMobile';
    const template_key_newRegister      = 'newRegister';
    const template_key_PasswordChange   = 'passwordChange';
    const template_key_PasswordReset    = 'passwordReset';
    const template_key_SetupDevices     = 'setupDevices';
    const template_key_ShareSendToEmail = 'shareSendToEmail';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mail_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_key', 'template_lang', 'template_from_email', 'template_subject', 'template_body_html', 'template_body_text'], 'required'],
            [['template_body_html', 'template_body_text'], 'string'],
            [['template_key', 'template_from_name'], 'string', 'max' => 30],
            [['template_lang'], 'string', 'max' => 3],
            [['template_from_email'], 'string', 'max' => 50],
            [['template_from_email'], 'email'],
            [['template_subject'], 'string', 'max' => 255],
            [['template_key', 'template_lang'], 'unique', 'targetAttribute' => ['template_key', 'template_lang'], 'message' => 'The combination of Variant of template and Language of template has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'template_id' => 'Id',
            'template_key' => 'Template key',
            'template_lang' => 'Template language',
            'template_from_email' => 'Sender email',
            'template_from_name' => 'Sender name',
            'template_subject' => 'Letter subject',
            'template_body_html' => 'Letter body HTML format',
            'template_body_text' => 'Letter body TEXT format',
        ];
    }

    /**
     * returns list of keys in array
     *
     * @return array
     */
    public static function keyLabels()
    {
        return [
            self::template_key_downloadMobile   => 'Ссылка на скачивание приложения',
            self::template_key_newRegister      => 'Подтверждение регистрации',
            self::template_key_PasswordChange   => 'Смена пароля пользователем',
            self::template_key_PasswordReset    => 'Восстановление пароля',
            self::template_key_SetupDevices     => 'Подключите устройства',
            self::template_key_ShareSendToEmail => 'Отправка ссылки на шару на емейл',
        ];
    }

    /**
     * return key name by key value
     * @param string $template_key
     *
     * @return string | null
     */
    public static function keyLabel($template_key)
    {
        $labels = self::keyLabels();
        return isset($labels[$template_key]) ? $labels[$template_key] : null;
    }

    /**
     * @param string $template_key
     * @param array $data
     *
     * @return bool;
     */
    public static function sendByKey($template_key, array $data)
    {
        if (isset($data['user']) && is_object($data['user'])) {
            $email_to = $data['user']->user_email;
        } elseif (isset($data['download']) && is_object($data['download'])) {
            $email_to = $data['download']->email;
        } else {
            $email_to = null;
        }
        if (isset($data['email_to'])) {
            $email_to = $data['email_to'];
        }
        if (!$email_to) {
            Yii::warning('Не передан емейл получателя');
            return false;
        }

        /** @var \common\models\Users $user */
        if (isset($data['user']) && is_object($data['user'])) { $user = &$data['user']; } else { $user = null; }
        /** @var \frontend\models\forms\DownloadMobileForm $download */
        if (isset($data['download']) && is_object($data['download'])) { $download = &$data['download']; } else { $download = null; }
        /** @var \common\models\UserFiles $UserFile */
        if (isset($data['UserFile']) && is_object($data['UserFile'])) { $UserFile = &$data['UserFile']; } else { $UserFile = null; }

        $tpl = self::findOne(['template_key' => $template_key, 'template_lang' => Yii::$app->language]);
        if ($tpl) {
            $search = [
                '{{app_name}}',
                '{{download-app-url}}',
                '{{confirm-registration-url}}',
                '{{change-password-url}}',
                '{{reset-password-url}}',
                '{{user_email}}',
                '{{user_name}}',
                '{{share_link}}',
            ];
            $replace = [
                Yii::$app->name,
                $download ? Yii::$app->urlManager->createAbsoluteUrl(['download']) : '',
                $user ? Yii::$app->urlManager->createAbsoluteUrl(['user/confirm-registration', 'token' => $user->password_reset_token]) : '',
                $user ? Yii::$app->urlManager->createAbsoluteUrl(['user/change-password', 'token' => $user->password_reset_token]) : '',
                $user ? Yii::$app->urlManager->createAbsoluteUrl(['user/reset-change-password', 'token' => $user->password_reset_token]) : '',
                $user ? $user->user_email : '',
                $user ? $user->user_name  : '',
                $UserFile ? UserFiles::getShareLink($UserFile->share_hash, $UserFile->is_folder) : '',
            ];

            return Yii::$app->mailer
                ->compose(['html' => 'universalTemplate-html', 'text' => 'universalTemplate-text'], [
                    'bodyHtml' => str_replace($search, $replace, $tpl->template_body_html),
                    'bodyText' => str_replace($search, $replace, $tpl->template_body_text),
                ])
                ->setTo($email_to)
                ->setFrom([$tpl->template_from_email => str_replace($search, $replace, $tpl->template_from_name)])
                ->setSubject(str_replace($search, $replace, $tpl->template_subject))
                //->setHtmlBody(str_replace($search, $replace, $tpl->template_body_html))
                //->setTextBody(str_replace($search, $replace, $tpl->template_body_text))
                ->send();
        } else {
            // + залогировать проблему
            Yii::warning('Не найден шаблон для отправки письма по ключу <' . $template_key . '> для языка '. Yii::$app->language);
            return false;
        }
    }
}
