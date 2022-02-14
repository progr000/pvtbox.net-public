<?php
/* @var $this yii\web\View */
/* @var $user \common\models\Users */

use yii\helpers\Url;
use common\models\Licenses;
use frontend\assets\v20190812\featuresAsset;

$this->title = Yii::t('app/features', 'title');

featuresAsset::register($this);

$user = Yii::$app->user->identity;
?>
<div class="content container">
    <h1 class="centered"><?= Yii::t('app/features', 'Private_Box_Features') ?></h1>
    <div class="page-section-description"><?= Yii::t('app/features', 'features__text') ?>
        <div class="down-pointer animated-item">
            <svg class="icon icon-down-arrow">
                <use xlink:href="#down-arrow"></use>
            </svg>
        </div>
    </div>
    <div class="features-table-container">
        <?= Yii::t('app/features', 'features_table') ?>
    </div>

    <h1 class="centered"><?= Yii::t('app/features', 'Each_version_has') ?></h1>
    <div class="features">
        <div class="features-item animated-item" id="speed-target">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-high-speed">
                    <use xlink:href="#high-speed"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__speed') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__speed_text') ?></div>
            </div>
        </div>
        <div class="features-item animated-item" id="security-target">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-security">
                    <use xlink:href="#security"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__security') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__security_text') ?></div>
            </div>
        </div>
        <div class="features-item animated-item" id="confidentiality-target">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-confidentiality">
                    <use xlink:href="#confidentiality"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__confidentiality') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__confidentiality_text') ?></div>
            </div>
        </div>
        <div class="features-item animated-item">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-people">
                    <use xlink:href="#people"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__collaboration') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__collaboration_text') ?></div>
            </div>
        </div>
        <div class="features-item animated-item">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-round-arrows">
                    <use xlink:href="#round-arrows"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__synchronization') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__synchronization_text') ?></div>
            </div>
        </div>
        <div class="features-item animated-item">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-admin">
                    <use xlink:href="#admin"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__capabilities') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__capabilities_text') ?></div>
            </div>
        </div>
        <div class="features-item animated-item">
            <div class="features-item__icon-wrap">
                <svg class="icon icon-platforms">
                    <use xlink:href="#platforms"></use>
                </svg>
            </div>
            <div class="features-item__body">
                <div class="features-item__title"><?= Yii::t('app/features', 'features__platforms') ?></div>
                <div class="features-item__desc"><?= Yii::t('app/features', 'features__platforms_text') ?></div>
            </div>
        </div>
    </div>
    <div class="get-app animated-item">
        <div class="get-app__title"><?= Yii::t('app/features', 'Get_Private') ?></div>

        <?php if (false) { ?>
        <div class="get-app__action">
            <?=
            Yii::$app->user->isGuest
                ? '<a class="get-app-link js-open-form" href="#" data-src="#auth-popup"  data-tab="2">' . Yii::t('app/features', 'Try_for_free') . '</a>' . '<span>' . Yii::t('app/features', 'or') . '</span>'
                : ''
            ?>
            <?=
            (Yii::$app->user->isGuest || in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL]))
                ? '<a class="get-app-btn btn primary-btn" href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/features', 'Purchase_now') . '</a>'
                : ''
            ?>
        </div>
        <?php } ?>
        <a class="get-app-btn btn primary-btn" href="<?= Url::to(['/pricing?scroll=feedback-form'], CREATE_ABSOLUTE_URL) ?>"><?= Yii::t('app/features', 'Contact_sales') ?></a>
        <br /><br />
        <?= (Yii::$app->user->isGuest || in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL]))
            ? ''//'<div class="get-app__footer">' . Yii::t('app/features', 'You_can_upgrade_any_time') . '</div>'
            : ''
        ?>
    </div>
</div>