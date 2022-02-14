<?php
/* @var $this \yii\web\View */
/* @var $user \common\models\Users */

use common\widgets\Alert;

?>
<!-- begin #flash-tpl -->
<div id="flash-tpl" style="display: none;">

    <span id="flash-request-password-reset-error"><?= Yii::t('app/flash-messages', 'RequestPasswordReset_error')?></span>

    <div class="alert">
        <button type="button" class="close close-alert" data-dismiss="alert" aria-hidden="true">×</button>
        <span class="flash-message">{flash-message}</span>
    </div>

</div>
<!-- end #flash-tpl -->

<!-- begin #alert-block-container -->
<div id="alert-block-container" class="container alert-messages">
    <?php echo Alert::widget(); ?>
</div>
<!-- end #alert-block-container -->


<!-- begin #alert-template -->
<div id="alert-template" style="display: none;">
    <div class="mc-snackbar">
        <div class="mc-snackbar-container mc-snackbar-container--snackbar-icon">
            <div class="mc-snackbar-icon success"></div>
            <p class="mc-snackbar-title">{alert-message}</p>
            <button class="mc-snackbar-actions mc-button-styleless mc-snackbar-close">
                <span class="mc-button-content"><?= Yii::t('app/flash-messages', 'Close') ?></span>
            </button>
        </div>
    </div>
</div>
<!-- end #alert-template -->

<!-- begin #alert-snackbar-container -->
<div class="mc-snackbar-holder-backdrop" id="alert-snackbar-container"></div>
<!-- end #alert-snackbar-container -->
