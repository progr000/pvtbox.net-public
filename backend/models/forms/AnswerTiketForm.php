<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use common\models\MailTemplatesStatic;
use common\models\Tikets;
use common\models\TiketsMessages;

/**
 * Login form
 */
class AnswerTiketForm extends Model
{
    public $tiket_id;
    public $message_text;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tiket_id', 'message_text'], 'required'],
            [['tiket_id'], 'integer'],
            [['message_text'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tiket_id' => 'Tiket ID',
            'message_text' => 'Текст ответа',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @return boolean whether the email was sent
     */
    public function sendEmail()
    {
        /* @var $tk \common\models\Tikets */
        $tk = Tikets::findOne(['tiket_id' => $this->tiket_id]);
        if ($tk) {
            $tkm = new TiketsMessages();
            $tkm->tiket_id     = $this->tiket_id;
            $tkm->message_text = $this->message_text;
            $tkm->user_id      = 0;
            $tkm->admin_id     = Yii::$app->user->identity->getId();
            if ($tkm->save()) {
                $tk->tiket_count_new_user += 1;
                $tk->save();

                $to = $tk->tiket_email; //Preferences::getValueByKey('adminEmail');
                //$from_name = "{$tk->tiket_name} <$tk->tiket_email}>";
                $subject = $tk->tiket_theme;

                return MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_standardMailTpl, $to, [
                    'user_name'      => $tk->tiket_name,
                    //'from_name'      => $from_name,
                    //'from_email'     => 'robot@pvtbox.net',
                    //'reply_to_email' => $tk->tiket_email,
                    //'reply_to_name'  => $tk->tiket_name,
                    'subject'        => $subject,
                    'body'           => $this->message_text,
                ]);

            }
        }
        return false;
    }
}
