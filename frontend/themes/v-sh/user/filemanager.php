<?php

/* @var $this yii\web\View */
/* @var $UserNode \common\models\UserNode */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $Server \common\models\Servers */
/* @var $ServerProxy \common\models\Servers */
/* @var $site_token string */
/* @var $User \common\models\Users */
/* @var $ShareElementForm \frontend\models\forms\ShareElementForm */
/* @var $uploadModel \frontend\models\forms\UploadFilesForm */

use frontend\assets\v20190812\elfinderAsset;

$this->title = Yii::t('user/filemanager', 'title');

/* assets */
elfinderAsset::register($this);
//$site_token = '6d599a1e954c135f5b08c51f083da9a9';
?>
<!-- begin file-manager content -->
<div class="content container filemanager"
     id="wss-data"
     data-old-id="div-for-download"
     data-token="<?= $site_token ?>"
     data-wss-url="wss://<?= isset($Server[0]) ? $Server[0]->server_url : 'null' ?>/ws/webfm/<?= $site_token ?>"
     data-signal-url="wss://<?= isset($Server[0]) ? $Server[0]->server_url : 'null' ?>/ws/webfm/<?= $site_token ?>?mode=get_file"
     data-proxy-url="<?= isset($ServerProxy[0]) ? $ServerProxy[0]->server_url : 'null' ?>/token/<?= $site_token ?>?file_name={file_name}&file_size={file_size}&event_uuid={last_event_uuid}"
     data-wss-url-echo-test-server="ws://echo.websocket.org">

    <div id="elfinderPanel" class="manager__panel" style="display: none;">

        <ul class="manager__menu">
            <!-- begin FOR TESTING -->
            <li><a class="btn-panel btn-panel--none btn-showNodes void-0 hidden-testing" href="#" title="<?= Yii::t('user/filemanager', 'Show nodes') ?>"></a></li>
            <li><a class="btn-panel btn-panel--none btn-addNode void-0 hidden-testing" href="#" title="<?= Yii::t('user/filemanager', 'Add node') ?>"></a></li>
            <!-- end FOR TESTING -->
            <!--<li><a class="btn-panel btn-panel- -home void-0" href="#" title="<?= Yii::t('user/filemanager', 'Home') ?>"></a></li>-->
            <li><a class="all-btn-panel btn-panel btn-panel--reload masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Reload') ?>"></a></li>
            <li class="li-btn-panel--back"><a class="all-btn-panel btn-panel btn-panel--back masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Back') ?>"></a></li>
            <li class="li-btn-panel--up"><a class="all-btn-panel btn-panel btn-panel--up masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Up') ?>"></a></li>
            <li><a class="all-btn-panel btn-panel btn-panel--download masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Download') ?>"></a></li>
            <li class="btn-hide-on-width-460"><a class="all-btn-panel btn-panel btn-panel--copy masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Copy') ?>"></a></li>
            <li class="btn-hide-on-width-460"><a class="all-btn-panel btn-panel btn-panel--cut masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Cut') ?>"></a></li>
            <li class="btn-hide-on-width-460"><a class="all-btn-panel btn-panel btn-panel--paste masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Paste') ?>"></a></li>
            <li class="btn-hide-on-width-460"><a class="all-btn-panel btn-panel btn-panel--rename masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Rename') ?>"></a></li>
            <li><a class="all-btn-panel btn-panel btn-panel--remove masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Delete') ?>"></a></li>
            <li><a class="all-btn-panel btn-panel btn-panel--view hide-deleted-files masterTooltip void-0" id="show-hide-deleted-button" href="#" title="<?= Yii::t('user/filemanager', 'Show_Hide_deleted') ?>"></a></li>
            <li class="btn-hide-on-width-325"><a class="all-btn-panel btn-panel btn-panel--structure btn-panel--structure-list masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Structure') ?>"></a></li>
            <li>
                <a class="all-btn-panel btn-panel btn-panel--sort masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Sort') ?>"></a>
                <div id="elfinder-sort-menu-list" class="ui-front ui-widget ui-widget-content elfinder-button-menu ui-corner-all" style="display: none;">
                    <div class="elfinder-button-menu-item" rel="name" data-folder-change="0">
                        <span class="ui-icon ui-icon-arrowthick-1-n my-ui-icon my-ui-icon-arrowthick-1-n"></span>
                        <span class="ui-icon ui-icon-arrowthick-1-s my-ui-icon my-ui-icon-arrowthick-1-s"></span>
                        by name
                    </div>
                    <div class="elfinder-button-menu-item" rel="size" data-folder-change="0">
                        <span class="ui-icon ui-icon-arrowthick-1-n my-ui-icon my-ui-icon-arrowthick-1-n"></span>
                        <span class="ui-icon ui-icon-arrowthick-1-s my-ui-icon my-ui-icon-arrowthick-1-s"></span>
                        by size
                    </div>
                    <div class="elfinder-button-menu-item" rel="kind" data-folder-change="0">
                        <span class="ui-icon ui-icon-arrowthick-1-n my-ui-icon my-ui-icon-arrowthick-1-n"></span>
                        <span class="ui-icon ui-icon-arrowthick-1-s my-ui-icon my-ui-icon-arrowthick-1-s"></span>
                        by kind
                    </div>
                    <div class="elfinder-button-menu-item" rel="date" data-folder-change="0">
                        <span class="ui-icon ui-icon-arrowthick-1-n my-ui-icon my-ui-icon-arrowthick-1-n"></span>
                        <span class="ui-icon ui-icon-arrowthick-1-s my-ui-icon my-ui-icon-arrowthick-1-s"></span>
                        by date
                    </div>
                    <div class="elfinder-button-menu-item elfinder-button-menu-item-separated" data-folder-change="1">
                        <span class="ui-icon ui-icon-check my-ui-icon my-icon-check"></span>
                        Folders first
                    </div>
                </div>
            </li>
            <li class="li-btn-panel--upload"><!--
            --><a class="all-btn-panel btn-panel btn-panel--upload locked-upload masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Upload_File') ?>"></a><!--
            --><a class="all-btn-panel btn-panel btn-panel--upload unlocked-upload btn-exec-uploadFile masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Upload_File') ?>"></a><!--
         --></li>
            <li class="li-btn-panel--folder"><a class="all-btn-panel btn-panel btn-panel--folder btn-exec-createFolder masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Create_folder') ?>"></a></li>
            <li class="li-btn-panel--search"><a class="all-btn-panel btn-panel btn-panel--search masterTooltip void-0" href="#" title="<?= Yii::t('user/filemanager', 'Search') ?>"></a></li>
        </ul>

        <ul class="manager__items">
            <li class="btn-hide-on-width-800"><!--
            --><a class="all-btn-panel btn-uploadFile locked-upload masterTooltip void-0" href="#" title="Upload File"><?= Yii::t('user/filemanager', 'Upload_File') ?></a><!--
            --><a class="all-btn-panel btn-uploadFile unlocked-upload btn-exec-uploadFile masterTooltip void-0" href="#" title="Upload File"><?= Yii::t('user/filemanager', 'Upload_File') ?></a><!--
         --></li>
            <li class="btn-hide-on-width-800"><a class="all-btn-panel btn-createFolder btn-exec-createFolder masterTooltip void-0" href="#" title="Create Folder"><?= Yii::t('user/filemanager', 'Create_folder') ?></a></li>
            <li class="item-search-form">
                <div class="manager-search">
                    <form onsubmit="return false;">
                        <input type="text" id="manager-search-text" class="manager-search__text" autocomplete="off" />
                        <input type=submit class="manager-search__button masterTooltip" alt="Search" title="<?= Yii::t('user/filemanager', 'Search') ?>" />
                        <input type=submit class="manager-searchreset__button masterTooltip" alt="Clear Search" title="<?= Yii::t('user/filemanager', 'Clear_Search') ?>" />
                    </form>
                </div>
            </li>
        </ul>

    </div>

    <div class="manager__content">
        <div id="elfinder" lang="<?= Yii::$app->language ?>" style="text-align: center; vertical-align: middle">
            <div style="padding: 150px;"><img src="/themes/v20190812/images/loading_v4.gif" alt="loading..." /></div>
        </div>
    </div>

</div>
<!-- end file-manager content -->


<?= $this->render('filemanager_buttons_tpl'); ?>

<?= $this->render('filemanager_modal', [
    'ShareElementForm' => $ShareElementForm,
    'User'             => $User,
]); ?>

