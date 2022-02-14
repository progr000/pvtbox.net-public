<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\Users;

/**
 * Login form
 */
class LoginFictive extends Model
{
    public $fu_id;
    public $fu_hs;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fu_id', 'fu_hs'], 'required'],
            ['fu_id', 'integer'],
            ['fu_hs', 'string', 'length' => 128],
        ];
    }

    /**
     * Validates the Hash for fictive login.
     *
     * @return bool
     */
    public function validateHash()
    {
        $user = $this->getUser();
        if ($user) {
            if ($this->fu_hs === hash("sha512", $user->user_email . $user->password_hash)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Logs in a user using the provided fu_hs.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function fictiveLogin()
    {
        if ($this->validate() && $this->validateHash()) {
            return Yii::$app->user->login($this->_user, 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[user_id]]
     *
     * @return Users|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Users::findIdentity($this->fu_id);
        }

        return $this->_user;
    }
}
