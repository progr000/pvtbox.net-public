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
                        switch ($status) {
                            case "success":
                                echo "Success";
                                break;
                            case "error":
                                echo "Error";
                                break;
                            default;
                                echo 'Error';
                        }
                    ?>
                    <a href="<?= Url::to(['/'], CREATE_ABSOLUTE_URL) ?>">Return</a>
                </div>

            </form>

        </div>

    </div>

</div>
<!-- END .payment -->

