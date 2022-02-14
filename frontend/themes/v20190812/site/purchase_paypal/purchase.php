<?php

/** @var $this yii\web\View */
/** @var string $id */
/** @var string $billed */
/** @var string $license */
/** @var \common\models\Users $User */
/** @var \frontend\models\forms\PurchaseForm $model */

use common\models\Licenses;
use frontend\models\forms\PurchaseForm;

$this->title = Yii::t('app/purchase', 'title');

?>
<div class="content container">

    <h1>Payment</h1>


    <?php
        if ($id == PurchaseForm::ID_SUCCESS) {

            echo $this->render('purchase-success', ['User' => $User, 'model' => $model]);

        } elseif ($id == PurchaseForm::ID_CANCEL) {

            echo $this->render('purchase-cancel', ['User' => $User, 'model' => $model]);

        } elseif ($id == PurchaseForm::ID_INITIALIZED) {

            echo $this->render('purchase-initialized', ['User' => $User, 'model' => $model]);

        } elseif ($id == PurchaseForm::ID_BUSINESS) {
            if ($billed == 'daily') {
                echo $this->render('purchase-test-business-daily', ['User' => $User, 'model' => $model]);
            } elseif ($billed == Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY)) {
                echo $this->render('purchase-business-monthly', ['User' => $User, 'model' => $model]);
            } else {
                echo $this->render('purchase-business-annually', ['User' => $User, 'model' => $model]);
            }
        } elseif ($id == PurchaseForm::ID_PROFESSIONAL) {
            if ($billed == 'daily') {
                echo $this->render('purchase-test-professional-daily', ['User' => $User, 'model' => $model]);
            } elseif ($billed == Licenses::getBilledByPeriod(Licenses::PERIOD_MONTHLY)) {
                echo $this->render('purchase-professional-monthly', ['User' => $User, 'model' => $model]);
            } else {
                echo $this->render('purchase-professional-annually', ['User' => $User, 'model' => $model]);
            }
        } else {

            echo $this->render('purchase-summary', [
                'User'    => $User,
                'model'   => $model,
                'billed'  => $billed,
                'license' => $license,
            ]);

        }
    ?>


</div>
