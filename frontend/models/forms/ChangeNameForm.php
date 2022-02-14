<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use common\models\Users;
use common\models\Licenses;

/**
 * Profile form
 *
 * @property string $user_name
 *
 */
class ChangeNameForm extends Model
{
    public $user_name;
    public $ChangeName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['user_name', 'required'],
            ['user_name', 'filter',  'filter' => 'trim'],
            ['user_name', 'string',  'min' => 2, 'max' => 50,
                /*
                'tooShort' => '{attribute} should  be at least 2 characters and not more than 50 characters',
                'tooLong'  => '{attribute} should  be at least 2 characters and not more than 50 characters',
                'message'  => '{attribute} should  be at least 2 characters and not more than 50 characters',
                */
            ],
            //['user_name', 'default', 'value' => Yii::$app->user->identity->user_name],
        ];
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
            'user_name' => Yii::t('forms/change-name-form', 'Company_name'),
        ];
    }

    /**
     * Change name for User
     *
     * @return bool
     */
    public function changeName()
    {
        $user = Users::findIdentity(Yii::$app->user->identity->getId());
        if ($user) {
            if ($user->license_type == Licenses::TYPE_PAYED_BUSINESS_ADMIN) {
                $user->user_company_name = $this->user_name;
            } else {
                $user->user_name = $this->user_name;
            }
            if ($user->save()) {
                return true;
            }
        }
        return false;
    }
}
