<?php

/* @var $software array */

use common\models\Software;

?>
<!-- begin #other-platforms -->
<ul class="platforms-download" id="other-platforms" style="display: none;">
    <?php
    foreach ($software as $k => $v) {
        if ($v['software_program_type'] == Software::PROGRAM_TYPE_FILE) {
            $v['software_url'] = Yii::$app->urlManager->createAbsoluteUrl(Yii::$app->params['downloadSoftwareDir'] . $v['software_file_name']);
        }
        $title = Yii::t('app/download', 'download');
        if ($v['software_type'] == Software::TYPE_ANDROID)
            $title  ="Get it on Google Play";
        if ($v['software_type'] == Software::TYPE_IOS)
            $title = "Get it on App Store";
        ?>
        <li><a class="btn xs-btn <?= (in_array($v['software_type'], [Software::TYPE_ANDROID, Software::TYPE_IOS])) ? $v['software_type'] : "primary" ?>-btn download-btn"
               data-version="<?= $v['software_version'] ?>"
               target="<?= (in_array($v['software_type'], [Software::TYPE_ANDROID, Software::TYPE_IOS])) ? "_blank" : "_self" ?>"
               rel="noopener"
               id="download-<?= $v['software_type'] ?>"
               title="<?= $title ?>"
               href="<?= $v['software_url'] ?>"><?= (in_array($v['software_type'], [Software::TYPE_ANDROID, Software::TYPE_IOS])) ? "" : Yii::t('app/download', 'download') ?></a><span><?= Software::getType($v['software_type']) ?> <?= $v['software_description'] ?></span></li>
        <?php
    }
    ?>
</ul>
<!-- end #other-platforms -->
