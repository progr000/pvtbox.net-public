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
class ChangeOoAddressForm extends Model
{
    public $user_oo_address;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //['user_oo_address', 'required'],
            ['user_oo_address', 'filter',  'filter' => 'trim'],
            ['user_oo_address', 'string', 'max' => 255],
            ['user_oo_address', 'url', 'defaultScheme' => 'https'],
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
            'user_oo_address' => Yii::t('forms/change-name-form', 'oo_address'),
        ];
    }

    /**
     * Change name for User
     * @param \common\models\Users $User
     * @return bool
     */
    public function changeOoAddress($User)
    {
        $User->user_oo_address = $this->user_oo_address;
        return $User->save();
    }
}
