<?php
/** @var $searchModelPayments \frontend\models\search\UserPaymentsSearch */
?>
<tr>
    <td><?= date(Yii::$app->params['datetime_format'], $searchModelPayments->_pay_date_ts) ?></td>
    <td>$<?= number_format($searchModelPayments->pay_amount, 2, '.', '') ?></td>
    <td><?= Yii::t('user/billing', 'pay_for_' . $searchModelPayments->pay_for) ?></td>
    <td><?= Yii::t('user/billing', $searchModelPayments->pay_type) ?></td>
    <td><span class="highlight-green"><?= Yii::t('user/billing', $searchModelPayments->pay_status) ?></span></td>
</tr>

