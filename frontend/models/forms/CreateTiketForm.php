<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\MailTemplatesStatic;
use common\models\Preferences;
use common\models\Tikets;
use common\models\TiketsMessages;

/**
 * Login form
 */
class CreateTiketForm extends Model
{
    public $tiket_theme;
    public $message_text;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tiket_theme', 'message_text'], 'required'],
            [['tiket_theme'], 'string', 'max' => 255],
            [['message_text'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tiket_theme'  => 'Ticket theme',
            'message_text' => 'Ticket text',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @return int|bool whether the email was sent
     */
    public function sendEmail()
    {
        /* @var \common\models\Users $user */
        $user = Yii::$app->user->identity;
        $tk  = new Tikets();
        $tk->user_id     = $user->getId();
        $tk->tiket_email = $user->user_email;
        $tk->tiket_name  = $user->user_name;
        $tk->tiket_theme = $this->tiket_theme;
        $tk->tiket_count_new_admin += 1;
        $tk->tiket_count_new_user   = 0;

        if ($tk->save()) {
            $tkm = new TiketsMessages();
            $tkm->tiket_id     = $tk->tiket_id;
            $tkm->message_text = $this->message_text;
            $tkm->user_id      = $tk->user_id;
            $tkm->save();

            $to = Preferences::getValueByKey('adminEmail');
            $from_name = "{$tk->tiket_name} <$tk->tiket_email}>";
            $subject = $tk->tiket_theme;

            if (MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
                'from_name'      => $from_name,
                //'from_email'     => 'robot@pvtbox.net',
                'reply_to_email' => $tk->tiket_email,
                'reply_to_name'  => $tk->tiket_name,
                'subject'        => $subject,
                'body'           => $this->message_text,
            ])) {

                return $tk->tiket_id;
            }
        }

        return false;
    }
}
