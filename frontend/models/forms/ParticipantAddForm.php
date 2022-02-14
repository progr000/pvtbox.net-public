<?php

namespace frontend\models\forms;

use common\models\UserConferences;
use Yii;
use yii\base\Model;
use common\models\Licenses;
use common\models\UserLicenses;
use common\models\MailTemplatesStatic;

/**
 * ShareElementForm is the model behind the contact form.
 *
 * @property string $participant_email
 * @property string $conference_guest_hash
 * @property \common\models\Users $User
 */
class ParticipantAddForm extends Model
{
    public $participant_email;
    public $conference_guest_hash;
    protected $User;

    /**
     * ParticipantAddForm constructor.
     * @param \common\models\Users $User
     * @param array $config
     */
    public function __construct($User, array $config=array())
    {
        parent::__construct($config);
        $this->User = $User;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['participant_email'], 'required'],
            [['participant_email'], 'email'],
            [['conference_guest_hash'], 'string', 'length' => 32],
            //[['participant_email'], 'checkAvailableLicenses'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'participant_email' => Yii::t('forms/share-element-form', 'email'),
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function checkAvailableLicenses($attribute, $params)
    {
        if ($this->User->license_type != Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
            $license_count_info = UserLicenses::getLicenseCountInfoForUser($this->User->user_id);
        } else {
            $license_count_info['unused'] = 10000;
        }
        if ($license_count_info['unused'] <= 0) {
            $this->addError($attribute, Yii::t('app/flash-messages', 'license_restriction_businessAdmin_invite_non_registered_but_no_available_licenses'));
        }
    }

    /**
     * @return array
     */
    public function guestLinkSendToEmail()
    {
        $Conference = UserConferences::findByGuestHash($this->conference_guest_hash);
        if ($Conference) {
            MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_GuestRoomLink, $this->participant_email, [
                'user_name'       => $this->participant_email,
                'guest_room_link' => $Conference->conference_guest_link,
            ]);
            return [
                'status' => true,
                'info' => "OK",
            ];
        } else {
            return [
                'status' => false,
                'info' => "Conference not found",
            ];
        }
    }

}
