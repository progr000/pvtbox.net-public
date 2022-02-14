<?php

use common\models\Users;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

/* @var $UserModel common\models\Users */

$data_value_in_progress = "In Progress...";
$data_value_ready_to_start = "Start Delete Old Patches For User";
?>

<label>restorePatchTTL (seconds): <input type="text" id="restore-patch-ttl" value="0" name="restorePatchTTL" /></label>
<input type="button"
       id="start-dop-for-user"
       value="<?= ($UserModel->user_dop_status == Users::DOP_IN_PROGRESS) ? $data_value_in_progress : $data_value_ready_to_start ?>"
       name="StartDOPForUser"
       data-user-id="<?= $UserModel->user_id ?>"
       data-value-in-progress="<?= $data_value_in_progress ?>"
       data-value-ready-to-start="<?= $data_value_ready_to_start ?>"
       <?= ($UserModel->user_dop_status == Users::DOP_IN_PROGRESS) ? 'class="not-Active" disabled="disabled"' : '' ?>
       />
&nbsp;
<input type="button"
       id="get-log-dop-for-user"
       value="Get Log"
       name="GetDOPLog"
       data-user-id="<?= $UserModel->user_id ?>" />
