<?php

/* @var $this yii\web\View */
/* @var $model \frontend\models\forms\PurchaseForm */

use yii\helpers\Url;
use common\models\Licenses;
use frontend\models\forms\PurchaseForm;

$this->title = Yii::t('app/purchase', 'title');

?>

<!-- .payment -->
<div class="payment">

    <div class="pricing__cont">
    
        <span class="title-min"><?= Yii::t('app/purchase', 'You_account_type') ?> <b><?= Licenses::getType($model->User->license_type) ?></b></span>

        <div class="payment__block">

            <form role="form">

                <div class="form-total">
                    <?php
                        echo Yii::t('app/purchase', 'ERROR_' . $model->error_code);
                        /*
                        switch ($model->error_code) {
                            case PurchaseForm::ERROR_BUSINESS_USER:
                                echo Yii::t('app/purchase', 'ERROR_BUSINESS_USER');
                                break;
                            case PurchaseForm::ERROR_CANT_DOWNGRADE:
                                echo Yii::t('app/purchase', 'ERROR_CANT_DOWNGRADE');
                                break;
                            case PurchaseForm::ERROR_ALREADY_PRO:
                                echo Yii::t('app/purchase', 'ERROR_ALREADY_PRO', [
                                    'license_professional' => Licenses::getType(Licenses::TYPE_PAYED_PROFESSIONAL),
                                ]);
                                break;
                            case PurchaseForm::ERROR_PERIOD_MISMATCH:
                                echo Yii::t('app/purchase', 'ERROR_PERIOD_MISMATCH');
                                break;
                            case PurchaseForm::ERROR_SUCCESS:
                                echo Yii::t('app/purchase', 'ERROR_SUCCESS');
                                break;
                            default;
                                echo 'Unknown error';
                        }
                        */
                    ?>
                    <a href="<?= Url::to(['/'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/purchase', 'Return') ?></a>
                </div>

            </form>

        </div>

    </div>

</div>
<!-- END .payment -->

