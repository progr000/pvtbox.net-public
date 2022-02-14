<?php

/* @var $software array */

use common\models\Software;

?>
<div id="other-platforms"  class="row" style="display: none; text-align: center">

    <?php
    foreach ($software as $k => $v) {
        if ($v['software_program_type'] == Software::PROGRAM_TYPE_FILE) {
            $v['software_url'] = Yii::$app->urlManager->createAbsoluteUrl(Yii::$app->params['downloadSoftwareDir'] . $v['software_file_name']);
        }
        ?>
        <div class="row os-type-<?= $v['software_type']; ?>" style="padding-top: 5px;">
            <div class="col-sm-3 col-xs-3 col-md-3"></div>
            <div class="col-sm-4 col-xs-5 col-md-4" style="font-size: 14px; text-align: right">
                <?= Software::getType($v['software_type']) ?> <?= $v['software_description'] ?>
            </div>
            <div class="col-sm-1 col-xs-3 col-md-1" style="text-align: left; padding-left: 0px;">
                <?php
                if (in_array($v['software_type'], [Software::TYPE_ANDROID, Software::TYPE_IOS])) {
                    ?>
                    <a class="btn-<?= $v['software_type'] ?>"
                       data-version="<?= $v['software_version'] ?>"
                       target="<?= (in_array($v['software_type'], [Software::TYPE_ANDROID, Software::TYPE_IOS])) ? "_blank" : "_self" ?>"
                       rel="noopener"
                       id="download-<?= $v['software_type'] ?>"
                       href="<?= $v['software_url'] ?>"><img alt="<?= $v['software_type'] == Software::TYPE_ANDROID ? "Get it on Google Play" : "Get it on App Store" ?>"
                                                             class="img-after-loader"
                                                             data-src-after="/themes/orange/images/download-image-<?= $v['software_type'] ?>.png"
                                                             src="" /></a>
                    <?php
                } else {
                    ?>
                    <a class="btn-min"
                       data-version="<?= $v['software_version'] ?>"
                       target="<?= (in_array($v['software_type'], [Software::TYPE_ANDROID, Software::TYPE_IOS])) ? "_blank" : "_self" ?>"
                       rel="noopener"
                       id="download-<?= $v['software_type'] ?>"
                       href="<?= $v['software_url'] ?>"><?= Yii::t('app/download', 'download') ?></a>
                    <?php
                }
                ?>
            </div>
            <div class="col-sm-4 col-xs-1 col-md-4"></div>
        </div>
        <?php
    }
    ?>

</div>