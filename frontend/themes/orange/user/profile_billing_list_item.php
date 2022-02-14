<?php

/** @var $browser string */
/** @var $status array */
/** @var $searchModelPayments \frontend\models\search\UserPaymentsSearch */

use common\helpers\Functions;
?>

<div class="table__body">
    <div class="table__body-box"><span><?= date(Yii::$app->params['datetime_format'], $searchModelPayments->_pay_date_ts) ?></span></div>
    <div class="table__body-box"><span>$<?= number_format($searchModelPayments->pay_amount, 2, '.', '') ?></span></div>
    <div class="table__body-box"><span><?= Yii::t('user/billing', 'pay_for_' . $searchModelPayments->pay_for) ?></span></div>
    <div class="table__body-box"><span><?= Yii::t('user/billing', $searchModelPayments->pay_type) ?></span></div>
    <div class="table__body-box"><span><?= Yii::t('user/billing', $searchModelPayments->pay_status) ?></span></div>
</div>
