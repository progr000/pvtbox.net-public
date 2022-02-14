<?php
/* @var $this yii\web\View */
/* @var $user \common\models\Users */

use common\models\Licenses;
use yii\helpers\Url;

$this->title = Yii::t('app/features', 'title');

$user = Yii::$app->user->identity;
?>
<!-- .features -->
<div class="features">
    <div class="features__cont">
        <div class="title">
            <h2><?= Yii::t('app/features', 'Private_Box_Features') ?></h2>
        </div>

        <div class="features__text">
            <p><?= Yii::t('app/features', 'features__text') ?></p>
        </div>

        <div class="features__block">
            <div class="features__box features__box--confidentiality" id="confidentiality">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__confidentiality') ?></h4>

                    <p><?= Yii::t('app/features', 'features__confidentiality_text') ?></p>
                </div>
            </div>

            <div class="features__box features__box--security" id="security">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__security') ?></h4>

                    <p><?= Yii::t('app/features', 'features__security_text') ?></p>
                </div>
            </div>

            <div class="features__box features__box--speed" id="speed">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__speed') ?></h4>

                    <p><?= Yii::t('app/features', 'features__speed_text') ?></p>
                </div>
            </div>

            <div class="features__box features__box--synchronization">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__synchronization') ?></h4>

                    <p><?= Yii::t('app/features', 'features__synchronization_text') ?></p>
                </div>
            </div>

            <div class="features__box features__box--collaboration">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__collaboration') ?></h4>

                    <p><?= Yii::t('app/features', 'features__collaboration_text') ?></p>
                </div>
            </div>

            <div class="features__box features__box--capabilities">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__capabilities') ?></h4>

                    <p><?= Yii::t('app/features', 'features__capabilities_text') ?></p>
                </div>
            </div>

            <div class="features__box features__box--platforms">
                <div class="features__box-cont">
                    <h4><?= Yii::t('app/features', 'features__platforms') ?></h4>

                    <p><?= Yii::t('app/features', 'features__platforms_text') ?></p>
                </div>
            </div>
        </div>

        <div class="title">
            <h2><?= Yii::t('app/features', 'Get_Private') ?></h2>
        </div>

        <div class="features__button">
            <?=
            Yii::$app->user->isGuest
                ? '<a class="features__button-link signup-dialog" href="#" data-toggle="modal" data-target="#entrance" data-whatever="reg">' . Yii::t('app/features', 'Try_for_free') . '</a>' .
                  '<span class="features__button-or">' . Yii::t('app/features', 'or') . '</span>'
                : ''
            ?>
            <?=
            (Yii::$app->user->isGuest || in_array($user->license_type, [Licenses::TYPE_FREE_DEFAULT, Licenses::TYPE_FREE_TRIAL]))
                ? '<a class="btn-average" href="' . Url::to(['/pricing'], CREATE_ABSOLUTE_URL) . '">' . Yii::t('app/features', 'Purchase_now') . '</a>' .
                  '<span class="features__button-info">' . Yii::t('app/features', 'You_can_upgrade_any_time') . '</span>'
                : ''
            ?>
        </div>
    </div>
</div>
<!-- END .features -->
