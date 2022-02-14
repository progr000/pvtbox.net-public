<?php
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use common\helpers\Functions;
use common\models\Users;
use common\models\MailTemplatesStatic;
use common\models\Licenses;
use common\models\UserLicenses;

/**
 * Signup form
 */
class RegisterUserBySellerForm extends Model
{
    public $user_name;
    public $user_email;
    public $password;
    public $password_repeat;
    public $seller_id;
    public $send_email_about_registration;
    public $has_personal_seller;
    public $create_business_account;
    public $license_count;
    public $server_license_count;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['user_email', 'password', 'password_repeat'], 'required'],
            [['seller_id'], 'integer', 'min' => 0],
            [['seller_id'], 'default', 'value' => null],
            [['user_name', 'user_email'], 'filter', 'filter' => 'trim'],
            ['user_name', 'string', 'min' => 2, 'max' => 50],
            ['user_email', 'email'],
            ['user_email', 'unique', 'targetClass' => '\common\models\Users', 'message' => 'This E-Mail address has already been taken.'],
            ['password', 'string', 'min' => 6],
            ['password', 'match','pattern' => Users::PASSWORD_PATTERN, 'message' => 'For the password you can use only letters of the Latin alphabet, digits and symbols !@#$%^&*()_+-=[]{}<>;:"\'\\|?/.,'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            [['has_personal_seller', 'send_email_about_registration', 'create_business_account'], 'integer'],
            [['has_personal_seller', 'send_email_about_registration', 'create_business_account'], 'in', 'range' => [Users::YES, Users::NO]],
            [['has_personal_seller', 'send_email_about_registration'], 'default', 'value' => Users::YES],
            [['create_business_account'], 'default', 'value' => Users::NO],
            [['license_count'], 'integer', 'min' => 1, 'max' => 99],
            [['server_license_count'], 'integer', 'min' => 0, 'max' => 9],
            [['create_business_account'], 'checkCountLicenses'],
        ];

        return $rules;
    }

    public function checkCountLicenses($attribute, $params)
    {
        if ($this->$attribute == Users::YES && $this->license_count < 1) {
            $this->addError($attribute, 'Invalid count license');
        }
    }

    /**
     * attribute for input fields.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'user_name'        => 'Full Name',
            'user_email'       => 'E-Mail',
            'password'         => 'Password',
            'password_repeat'  => 'Retype password',
            'license_count'    => 'User license count',
        ];
    }

    /**
     * Signs user up.
     *
     * @return Users|null the saved model or null if saving fails
     */
    public function createUser()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $user                 = new Users();
        $user->user_name      = $this->user_name
            ? $this->user_name
            : Functions::getNameFromEmail($this->user_email);
        $user->user_email     = $this->user_email;
        $user->license_type   = Licenses::TYPE_FREE_TRIAL;
        $user->license_expire = date(SQL_DATE_FORMAT, time() + Licenses::getCountDaysTrialLicense() * 86400);
        $user->user_last_ip   = Yii::$app->request->getUserIP();
        $user->user_ref_id    = $this->seller_id;
        $user->has_personal_seller = $this->has_personal_seller;

        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generatePasswordResetToken();

        if ($user->save()) {

            if ($this->create_business_account == Users::YES) {
                $user->license_type   = Licenses::TYPE_PAYED_BUSINESS_ADMIN;
                $user->license_period = Licenses::PERIOD_MONTHLY;
                $user->pay_type       = Users::PAY_CARD;
                $user->payment_already_initialized = Users::PAYMENT_INITIALIZED;

                $lic_start = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time()));
                $lic_end = $user->license_expire;
                $lic_lastpay = time();
                $lic_group_id = time();

                for ($i = 1; $i <= $this->license_count; $i++) {
                    $lic = new UserLicenses();
                    $lic->lic_start = $lic_start;
                    $lic->lic_end = $lic_end;
                    $lic->lic_period = $user->license_period;
                    $lic->lic_owner_user_id = $user->user_id;
                    $lic->lic_colleague_user_id = ($i == 1) ? $user->user_id : null;
                    $lic->lic_colleague_email = ($i == 1) ? $user->user_email : null;
                    $lic->lic_lastpay_timestamp = $lic_lastpay;
                    $lic->lic_group_id = $lic_group_id;
                    if (!$lic->save()) {
                        Yii::$app->session->setFlash('danger', Json::encode($lic->getErrors()));
                        $transaction->rollBack();
                        //var_dump($lic->getErrors()); exit;
                        return false;
                    }
                }

                UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $user->user_id]);

                $user->license_expire = $lic_end;
                $user->license_count_available = $this->license_count - 1;
                $user->license_count_used = 1;

            }

            if ($user->save()) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
                return false;
            }

            if ($this->send_email_about_registration) {
                MailTemplatesStatic::sendByKey(MailTemplatesStatic::template_key_newRegister, $user->user_email, ['UserObject' => $user]);
            }

            //Yii::$app->session->setFlash('success', 'User successfuly created.');
            return $user;
        }

        $transaction->rollBack();
        return false;
        //Yii::$app->session->setFlash('danger', 'There was an error on creating new User.');
    }
}
