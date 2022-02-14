<?php
namespace frontend\models\forms;

use Yii;
use yii\base\Model;

/**
 * Profile form
 *
 * @property string $user_name
 *
 */
class ConferenceAddForm extends Model
{
    public $conference_name;
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['conference_name', 'required'],
            ['conference_name', 'filter',  'filter' => 'trim'],
            ['conference_name', 'string', 'min' => 5, 'max' => 50],
            [['conference_name'],
                'unique',
                'targetClass' => '\common\models\UserConferences',
                'targetAttribute' => ['conference_name', 'user_id'],
                'message' => 'Conference with this name already exist.',
            ],
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
            'user_oo_address' => Yii::t('forms/change-name-form', 'conference_name'),
        ];
    }

    /**
     * Change name for User
     * @param \common\models\Users $User
     * @return bool
     */
    public function createConference($User)
    {
        //$User;
    }
}
