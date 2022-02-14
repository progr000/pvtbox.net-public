<?php
/* @var $this yii\web\View */
/* @var $user \common\models\Users */

use common\models\Preferences;

$this->title = Yii::t('app/about', 'title');
?>
<div class="content container editor-area">
    <?= Yii::t('app/about', 'html_text', [
        'supportEmail_TECHNICAL' => Preferences::getValueByKey('supportEmail_TECHNICAL'),
        'supportEmail_LICENSES'  => Preferences::getValueByKey('supportEmail_LICENSES'),
        'supportEmail_OTHER'     => Preferences::getValueByKey('supportEmail_OTHER'),
        'adminEmail'             => Preferences::getValueByKey('adminEmail'),
    ]) ?>
</div>
