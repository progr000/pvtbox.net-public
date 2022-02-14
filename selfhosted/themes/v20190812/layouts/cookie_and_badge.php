<?php
if (Yii::$app->user->isGuest) { /*
    ?>
<!-- begin #respect-privacy-badge -->
<div class="badge-info msg-popup opened" id="respect-privacy-layer" style="display: none;">
    <div class="msg-popup__title"><?= Yii::t('app/common', 'At_private_respect_privacy', ['APP_NAME' => Yii::$app->name]); ?></div>
    <div class="msg-popup__text">
        <ol class="number-list">
            <li><?= Yii::t('app/common', 'Store_files_on_your_devices'); ?></li>
            <li><?= Yii::t('app/common', 'Have_no_access_to_your_files'); ?></li>
            <li><?= Yii::t('app/common', 'Never_sell_your_information'); ?></li>
        </ol>
    </div>
    <button class="msg-popup__btn btn primary-btn wide-btn sm-btn respect-privacy-layer__button badge-link__btn respect-privacy-close" type="button"><?= Yii::t('app/common', 'OK') ?></button>
    <button class="btn popup-close-btn respect-privacy-layer__button respect-privacy-close" type="button" title="<?= Yii::t('app/common', "Close") ?>">
        <svg class="icon icon-close">
            <use xlink:href="#close"></use>
        </svg>
    </button>
</div>
<!-- end #respect-privacy-badge -->
    <?php
*/ }
?>

<!-- begin #cookie-policies-layer -->
<div class="cookie-info opened" id="cookie-policies-layer" style="display: none;">
    <div class="container">
        <svg class="icon icon-info">
            <use xlink:href="#info"></use>
        </svg>
        <div class="cookie-info__text">
            <p><?= Yii::t('app/common', 'We_use_cookies'); ?></p>
        </div>
        <button class="cookie-info__close-btn btn primary-btn sm-btn cookie-layer__button" type="button"><?= Yii::t('app/common', 'OK') ?></button>
    </div>
</div>
<!-- end #cookie-policies-layer -->
