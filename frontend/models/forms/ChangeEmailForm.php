<?php
namespace frontend\models\forms;

use common\models\UserColleagues;
use Yii;
use yii\base\Model;
use common\models\Users;

/**
 * Profile form
 */
class ChangeEmailForm extends Model
{
    public $user_email;
    public $password;
    public $token;
    public $ChangeEmail;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_email', 'password'], 'required'],
            ['user_email', 'filter', 'filter' => 'trim'],
            ['user_email', 'email'],
            ['user_email', 'checkUnique'],
            ['password', 'string'],
            ['password', 'checkCorrectPassword'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function checkUnique($attribute, $params)
    {
        $user = Users::findByEmail($this->user_email);
        if ($user) {
            if ($user->user_email == Yii::$app->user->identity->user_email) {
                $this->addError($attribute, Yii::t('forms/change-email-form', 'email_identical_to_old'));
            } else {
                $this->addError($attribute, Yii::t('forms/change-email-form', 'email_already_in_use'));
            }
        }
    }

    public function checkCorrectPassword($attribute, $params)
    {
        /** @var \common\models\Users $User */
        $User = Yii::$app->user->identity;
        if (!$User->validatePassword($this->password)) {
            $this->addError($attribute, 'Wrong password');
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
            'token' => '',
            'user_email' => Yii::t('forms/change-email-form', 'Enter_new_email'),
            'password'   => Yii::t('forms/change-email-form', 'Enter_your_password'),
        ];
    }

    /**
     * Change Email
     *
     * @return bool
     */
    public function changeEmail()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $user = Users::findIdentity(Yii::$app->user->identity->getId());
        if ($user) {
            /*
            $query = "SELECT *
                      FROM {{%user_collaborations}} as t1
                      INNER JOIN {{%user_colleagues}} as t2 ON t1.collaboration_id = t2.collaboration_id
                      WHERE (t1.user_id=:user_id)
                      AND (t2.colleague_permission != :PERMISSION_OWNER)
                      AND (t2.colleague_email = :new_user_email)";
            */
            $query = "DELETE FROM {{%user_colleagues}}
                      USING {{%user_collaborations}}
                      WHERE {{%user_colleagues}}.collaboration_id = {{%user_collaborations}}.collaboration_id
                      AND ({{%user_collaborations}}.user_id = :user_id)
                      AND ({{%user_colleagues}}.colleague_permission != :PERMISSION_OWNER)
                      AND ({{%user_colleagues}}.colleague_email = :new_user_email)
                      AND ({{%user_colleagues}}.user_id IS NULL)";

            $res = Yii::$app->db->createCommand($query, [
                'user_id'          => $user->user_id,
                'PERMISSION_OWNER' => UserColleagues::PERMISSION_OWNER,
                'new_user_email'   => $this->user_email,
            ])->execute();
            //var_dump($res); exit;

            //$_old_email = $user->user_email;
            $user->user_email = $this->user_email;
            $user->user_status = Users::STATUS_ACTIVE;
            $user->user_hash = md5($this->user_email);
            $user->user_remote_hash = Users::generateUserRemoteHash($this->user_email, hash('sha512', $this->password));
            $user->user_closed_confirm = Users::CONFIRM_UNCLOSED;
            if ($user->save()) {
                Yii::$app->session->set('alert_confirm_email', true);
                $transaction->commit();
                return true;
            }
        }
        $transaction->rollBack();
        return false;
    }
}
