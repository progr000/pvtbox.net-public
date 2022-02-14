<?php
namespace backend\models\forms;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use common\helpers\Functions;
use common\models\UserLicenses;

/**
 * Password reset form
 */
class AddLicenseCount extends Model
{
    public $license_count;

    /**
     * @var \common\models\Users
     */
    private $_user;


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'license_count'    => 'Count licenses',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_count'], 'required'],
            [['license_count'], 'integer', 'min' => 1, 'max' => 999],
        ];
    }

    /**
     * @param \common\models\Users $User
     *
     * @return bool
     */
    public function add($User)
    {
        $transaction = Yii::$app->db->beginTransaction();

        //$lic_end_timestamp = strtotime($User->license_expire);
        $lic_end_timestamp = time() + $User->license_period * 86400;
        $lic_start = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp(time()));
        $lic_end = date(SQL_DATE_FORMAT, Functions::getTimestampEndOfDayByTimestamp($lic_end_timestamp));
        //$lic_end = $User->license_expire;
        $lic_lastpay = time();
        $lic_group_id = time();
        $test = UserLicenses::findOne([
            'lic_colleague_user_id' => $User->user_id,
            'lic_owner_user_id' => $User->user_id,
        ]);
        for ($i = 1; $i <= $this->license_count; $i++) {
            $lic = new UserLicenses();
            $lic->lic_start = $lic_start;
            $lic->lic_end = $lic_end;
            $lic->lic_period = $User->license_period;
            $lic->lic_owner_user_id = $User->user_id;
            $lic->lic_colleague_user_id = (!$test && $i == 1) ? $User->user_id : null;
            $lic->lic_colleague_email = (!$test && $i == 1) ? $User->user_email : null;
            $lic->lic_lastpay_timestamp = $lic_lastpay;
            $lic->lic_group_id = $lic_group_id;
            if (!$lic->save()) {
                Yii::$app->session->setFlash('danger', Json::encode($lic->getErrors()));
                $transaction->rollBack();
                return false;
            }
        }

        UserLicenses::updateAll(['lic_end' => $lic_end], ['lic_owner_user_id' => $User->user_id]);

        $User->license_expire = $lic_end;
        $User->license_count_available += $this->license_count;

        if ($User->save()) {
            $transaction->commit();
            return true;
        } else {
            Yii::$app->session->setFlash('danger', Json::encode($User->getErrors()));
        }

        $transaction->rollBack();
        return false;
    }

}
