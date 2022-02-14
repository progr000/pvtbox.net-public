<?php

/* @var $this yii\web\View */
/* @var $UserNode \common\models\UserNode */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $Server \common\models\Servers */
/* @var $site_token string */
/* @var $User \common\models\Users */
/* @var $ShareElementForm \frontend\models\forms\ShareElementForm */
/* @var $uploadModel \frontend\models\forms\UploadFilesForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\UserFiles;
use common\models\UserColleagues;
use common\models\Licenses;
use frontend\assets\orange\elfinderAsset;

//var_dump(Yii::$app->session->getId());
$this->title = Yii::t('user/filemanager', 'title');

/* assets */
elfinderAsset::register($this);
//$site_token = '6d599a1e954c135f5b08c51f083da9a9';
?>

<!-- .manager -->
<div class="manager">
    <div class="manager__cont">

        <!--
        <div class="manager__button">
            <a class="btn-showNodes" href="javascript:void(0)">Show nodes</a>
            <a class="btn-addNode" href="javascript:void(0)">Add node</a>
        </div>
        -->
        <div style="display: none;"
             id="div-for-download"
             data-token="<?= $site_token ?>"
             data-signal-url="wss://signalserver.pvtbox.net:443/ws/webfm/<?= $site_token ?>?mode=get_file"
             data-proxy-url="https://proxy.pvtbox.net/token/<?= $site_token ?>?file_name={file_name}&file_size={file_size}&event_uuid={last_event_uuid}">
        </div>

        <div id="elfinderPanel" class="manager__palel" style="display: none;">

            <ul class="manager__menu">
                <!--<li><a class="btn-palel btn-palel--home" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Home') ?>"></a></li>-->
                <li><a class="all-btn-palel btn-palel btn-palel--reload masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Reload') ?>"></a></li>
                <li class="li-btn-palel--back"><a class="all-btn-palel btn-palel btn-palel--back masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Back') ?>"></a></li>
                <li class="li-btn-palel--up"><a class="all-btn-palel btn-palel btn-palel--up masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Up') ?>"></a></li>
                <li><a class="all-btn-palel btn-palel btn-palel--download masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Download') ?>"></a></li>
                <li class="btn-hide-on-width-450"><a class="all-btn-palel btn-palel btn-palel--copy masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Copy') ?>"></a></li>
                <li class="btn-hide-on-width-450"><a class="all-btn-palel btn-palel btn-palel--cut masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Cut') ?>"></a></li>
                <li class="btn-hide-on-width-450"><a class="all-btn-palel btn-palel btn-palel--paste masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Paste') ?>"></a></li>
                <li class="btn-hide-on-width-450"><a class="all-btn-palel btn-palel btn-palel--rename masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Rename') ?>"></a></li>
                <li><a class="all-btn-palel btn-palel btn-palel--remove masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Delete') ?>"></a></li>
                <li><a class="all-btn-palel btn-palel btn-palel--view hide-deleted-files masterTooltip" id="show-hide-deleted-button" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Show_Hide_deleted') ?>"></a></li>
                <li class="btn-hide-on-width-325"><a class="all-btn-palel btn-palel btn-palel--structure btn-palel--structure-list masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Structure') ?>"></a></li>
                <li>
                    <a class="all-btn-palel btn-palel btn-palel--sort masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Sort') ?>"></a>
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
                <li class="li-btn-palel--upload"><a class="all-btn-palel btn-palel btn-palel--upload btn-exec-uploadFile masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Upload_File') ?>"></a></li>
                <li class="li-btn-palel--folder"><a class="all-btn-palel btn-palel btn-palel--folder btn-exec-createFolder masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Create_folder') ?>"></a></li>
                <li class="li-btn-palel--search"><a class="all-btn-palel btn-palel btn-palel--search masterTooltip" href="javascript:void(0)" title="<?= Yii::t('user/filemanager', 'Search') ?>"></a></li>
            </ul>

            <ul class="manager__items">
                <li class="btn-hide-on-width-800"><a class="all-btn-palel btn-uploadFile btn-exec-uploadFile masterTooltip" href="javascript:void(0)" title="Upload File"><?= Yii::t('user/filemanager', 'Upload_File') ?></a></li>
                <li class="btn-hide-on-width-800"><a class="all-btn-palel btn-createFolder btn-exec-createFolder masterTooltip" href="javascript:void(0)" title="Create Folder"><?= Yii::t('user/filemanager', 'Create_folder') ?></a></li>
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

                <div style="display: none" id="SignUrl" data-token="<?= $site_token ?>">wss://<?= $Server[0]->server_url ?>/ws/webfm/<?= $site_token ?></div>

                <div id="elfinder" lang="<?= Yii::$app->language ?>" style="text-align: center; vertical-align: middle">

                    <!--
                    https://preloaders.net/ru/circular

                    https://htmler.ru/2011/05/13/top7-online-loading-generators/
                    http://www.chimply.com/Generator#classic-spinner,turningArrow
                    -->
                    <div style="padding: 150px;"><img src="/themes/orange/images/loading_v4.gif" alt="loading..." /></div>

                </div>


        </div>

    </div>
</div>
<!-- END .manager -->

<!-- BEGIN .Buttons Template -->
<div id="buttons-template" style="display: none;">

    <!-- #tpl-share-link-and-colleague-list -->
    <div id="tpl-share-link-and-colleague-list">
        <div class="workspace-sub__box">
            <div class="dropdown-share dropdown" id="shareDropMenu_{hash}">
                <div class="dropdown-toggle" data-toggle="dropdown" id="buttonDropMenu_{hash}" onclick="showShareDropMenu('{hash}')"><?= Yii::t('user/filemanager', 'Share') ?></div>
                <ul class="dropdown-menu">
                    <li><span data-toggle="modal" data-target="#getLink" onclick="showShareDialog('{hash}')"><?= Yii::t('user/filemanager', 'Get_link') ?></span></li>
                    <li><span data-toggle="modal" data-target="#settings" onclick="showColleagueList('{hash}')"><?= Yii::t('user/filemanager', 'Colleague_list') ?></span></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- #tpl-share-link-and-collaboration-settings -->
    <div id="tpl-share-link-and-collaboration-settings">
        <div class="workspace-sub__box">
            <div class="dropdown-share dropdown" id="shareDropMenu_{hash}">
                <div class="dropdown-toggle" data-toggle="dropdown" id="buttonDropMenu_{hash}" ontouchstart="showShareDropMenu('{hash}', event)" onclick="showShareDropMenu('{hash}', event)"><?= Yii::t('user/filemanager', 'Share') ?></div>
                <ul class="dropdown-menu">
                    <li><span class="share-collaborate-exec" data-toggle="modal" data-target="#getLink" ontouchstart="exec_share('{hash}', event)" onclick="exec_share('{hash}', event)"><?= Yii::t('user/filemanager', 'Get_link') ?></span></li>
                    <li><span class="share-collaborate-exec" data-toggle="modal" data-target="#settings" ontouchstart="showCollaborationDialog('{hash}', event)" onclick="showCollaborationDialog('{hash}', event)"><?= Yii::t('user/filemanager', 'Collaboration_Settings') ?></span></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- #tpl-share-link-only -->
    <div id="tpl-share-link-only">
        <div class="workspace-sub__box">
            <div class="dropdown-share dropdown-share-empty" data-toggle="modal" data-target="#getLink">
                <div class="dropdown-toggle" ontouchstart="exec_share('{hash}', event)" onclick="exec_share('{hash}', event)"><?= Yii::t('user/filemanager', 'Share') ?></div>
            </div>
        </div>
    </div>

</div>


<!-- BEGIN .Modal #share-create-remove-modal -->
<div class="modal fade" id="share-create-remove-modal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <ul class="nav nav-tabs" style="display: none;">
                <li class="active"><a href="#link-get" data-toggle="tab"></a></li>
                <li><a href="#link-get-active" data-toggle="tab"></a></li>
                <li><a href="#link-settings" data-toggle="tab"></a></li>
            </ul>

            <div class="tab-content" role="tablist">


                <div class="tab-pane active" role="tabpanel" id="link-get">

                    <div class="modal-header">
                        <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>
                    </div>

                    <div class="modal-body">

                        <div class="form-block">

                            <span class="modal-title">Get link</span>

                            <div class="form-group">
                                <span class="btn-empty-big create-share-button" _href="#link-get-active" data-toggle="tab"><?= Yii::t('user/filemanager', 'Create_link') ?></span>
                            </div>

                            <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Send_link_to_email') ?></span>

                            <div class="form-group">
                                <span class="form-control"><?= Yii::t('user/filemanager', 'Email') ?></span>
                            </div>

                            <span class="btn-big btn-notActive"><?= Yii::t('user/filemanager', 'Send') ?></span>

                        </div>

                    </div>

                </div>


                <div class="tab-pane" role="tabpanel" id="link-get-active">

                    <div class="modal-header">
                        <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>
                    </div>

                    <div class="modal-body">

                        <?php
                        $form = ActiveForm::begin([
                            'id' => 'share-send-to-email-form',
                            'enableClientValidation' => true,
                            'options' => [
                                'onsubmit' => 'return false',
                            ],
                        ]);
                        ?>
                        <div class="form-block">

                            <span class="modal-title"><?= Yii::t('user/filemanager', 'Get_link') ?></span>

                            <div class="form-group">
                                <input type="hidden" name="filesystem_hash" id="filesystem-hash" />
                                <input type="hidden" name="share_hash" id="share-hash" />
                                <textarea class="form-control form-control-textarea notActive" id="share-link-field" readonly="readonly"></textarea>
                            </div>

                            <div class="form-group__cont">
                                <div class="form-group__row">
                                    <div class="form-group__col"><a class="btn-empty copy-button" href="javascript:void(0)" data-clipboard-action="copy" data-clipboard-target="#share-link-field"><?= Yii::t('user/filemanager', 'Copy_link') ?></a></div>
                                    <div class="form-group__col"><a class="btn-empty remove-share-button" href="javascript:void(0)"><?= Yii::t('user/filemanager', 'Delete_link') ?></a></div>
                                    <div class="form-group__col"><span class="btn-empty" href="#link-settings" data-toggle="tab"><?= Yii::t('user/filemanager', 'Link_settings') ?></span></div>
                                </div>
                            </div>

                            <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Send_link_to_email') ?></span>

                            <div class="form-group">
                                <?=
                                $form->field($ShareElementForm, 'share_email')
                                    ->textInput([
                                        'id' => "share-email",
                                        'placeholder' => "E-mail",
                                        'autocomplete' => "off",
                                    ])
                                    ->label(false)
                                ?>
                            </div>

                            <?= Html::submitButton(Yii::t('user/filemanager', 'Send'), ['class' => "btn-big", 'name' => "share-send-to-email-button"]) ?>

                        </div>
                        <?php
                        ActiveForm::end();
                        ?>

                    </div>

                </div>


                <div class="tab-pane" role="tabpanel" id="link-settings">

                    <div class="modal-header">
                        <div class="btn-back" href="#link-get-active" data-toggle="tab"></div>
                        <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>
                    </div>

                    <div class="modal-body">

                        <?php
                        $form = ActiveForm::begin([
                            'id' => 'share-create-remove-form',
                            'enableClientValidation' => true,
                            'options' => [
                                'onsubmit' => 'return false',
                            ],
                        ]);
                        ?>
                        <div class="form-block">

                            <div id="info-settings-link-update-to-pro" style="display: none;"><?= Yii::t('user/filemanager', 'Settings_are_available_for') ?></div>
                            <a id="settings-link-update-to-pro" class="btn-min" href="<?= Url::to(['/pricing'], CREATE_ABSOLUTE_URL) ?>"><?=  Yii::t('user/filemanager', 'Update_to_pro_business', ['type_licenses' => Licenses::getType(Licenses::TYPE_PAYED_PROFESSIONAL) . '/' . Licenses::getType(Licenses::TYPE_PAYED_BUSINESS_ADMIN)]) ?></a>

                            <span class="modal-title"><?= Yii::t('user/filemanager', 'Set_expiry_date') ?></span>

                            <div class="form-group">
                                <div id="share-ttl-div" class="select select-color-orange masterTooltip" title="" title_payed="" title_unpayed="<?= Yii::t('user/filemanager', 'Available_for_PRO_Business') ?>">
                                    <select id="share-ttl" class="selectpicker" data-size="4" data-actionsBox="true" disabled="disabled">
                                        <?php
                                        $ttl_variants = UserFiles::ttlLabels();
                                        foreach ($ttl_variants as $k=>$v) {
                                            echo '<option value="' . $k . '">' . $v . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <span class="modal-title modal-title-indenting"><?= Yii::t('user/filemanager', 'Set_password') ?></span>

                            <div class="form-group">
                                <?=
                                $form->field($ShareElementForm, 'share_password')
                                     ->passwordInput([
                                         'id'            => "share-password",
                                         'placeholder'   => Yii::t('user/filemanager', 'Password'),
                                         'readonly'      => "readonly",
                                         'data-toggle'   => "password",
                                         'class'         => "form-control input-notActive masterTooltip",
                                         'title_payed'   => '',
                                         'title_unpayed' => Yii::t('user/filemanager', 'Available_for_PRO_Business'),
                                         'title'         => ''
                                     ])
                                     ->label(false)
                                ?>
                            </div>

                            <?= Html::submitButton(Yii::t('user/filemanager', 'Set'), [
                                'id'       => "share-settings-button",
                                'class'    => "btn-big btn-notActive masterTooltip",
                                //'name'     => "share-settings-button",
                                //'disabled' => "disabled",
                                'title_payed'   => '',
                                'title_unpayed' => Yii::t('user/filemanager', 'Available_for_PRO_Business'),
                                'title'         => '',
                            ]) ?>

                        </div>
                        <?php
                        ActiveForm::end();
                        ?>

                    </div>

                </div>


            </div>


        </div>

    </div>

</div>
<!-- END .Modal #share-create-remove-modal -->

<!-- BEGIN .Modal #collaborate-modal -->
<div class="modal modal-settings fade" id="collaborate-modal" tabindex="-1" role="dialog">

        <div class="modal-dialog" role="document">

            <div class="modal-content">

                <div class="modal-header">

                    <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>

                </div>

                <div class="modal-body">

                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'collaborate-form',
                        'enableClientValidation' => true,
                        'options' => [
                            'onsubmit' => 'return false',
                        ],
                    ]);
                    ?>
                    <div class="form-block">

                        <input type="hidden" name="collaborate_filesystem_hash" id="collaborate-filesystem-hash" />
                        <input type="hidden" name="collaborate_file_uuid" id="collaborate-file-uuid" />

                        <span class="modal-title"><?= Yii::t('user/filemanager', 'Invite_colleagues_to') ?> <p id="collaborate-file-name" class="masterTooltip" title="">file-name</p></span>

                        <div class="modal-settCont" id="collaborate-user-new">

                            <div class="modal-settCont__box">
                                <?=
                                $form->field($ShareElementForm, 'share_email')
                                    ->textInput([
                                        'id' => "colleague-email",
                                        //'type' => "email",
                                        'placeholder' => "Colleague email",
                                        'autocomplete' => "off",
                                    ])
                                    ->label(false)
                                ?>
                            </div>

                            <div class="modal-settCont__box">

                                <div class="dropdown-actions dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('app/common', 'Can') ?><span id="collaborate-user-access-type-new" data-action="<?= UserColleagues::PERMISSION_VIEW ?>"><?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?></span></div>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:void(0)" class="ch-user-collaborate-access-new" data-tokens="new" data-action="<?= UserColleagues::PERMISSION_EDIT ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?></a></li>
                                        <li><a href="javascript:void(0)" class="ch-user-collaborate-access-new" data-tokens="new" data-action="<?= UserColleagues::PERMISSION_VIEW ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?></a></li>
                                    </ul>
                                </div>

                            </div>

                            <div id="waiting-tpl" style="display: none;">
                                <div class="small-loading waiting-form"><?= Yii::t('user/filemanager', 'Loading') ?> <img class="loading" src="/themes/orange/images/loading_v4.gif" alt="loading..." /></div>
                            </div>

                            <div id="owner_tpl" style="display: none;">
                                <div class="table__body is-owner" id="collaborate-user-owner">
                                    <div class="table__body-box"><div class="userId color-<?= $User->_color ?>"><strong><?= $User->_sname ?></strong></div></div>
                                    <div class="table__body-box"><b class="colleague-email"><?= $User->user_email ?></b></div>
                                    <div class="table__body-box"><b class="table-status"><?= UserColleagues::statusLabel(UserColleagues::STATUS_JOINED) ?></b><i></i></div>
                                    <div class="table__body-box">

                                        <div class="dropdown-actions-view">
                                            <div class="dropdown-toggle">
                                                <span class="isorcan"><?= Yii::t('app/common', 'Is') ?></span>
                                                <span class="access-name"><?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_OWNER) ?></span>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="table__body-box"></div>
                                </div>
                            </div>
                            <div id="colleagues_tpl" style="display: none;">
                                <div class="table__body {owner_or_colleague}" id="collaborate-user-{colleague_id}">
                                    <div class="table__body-box"><div class="userId color-{color}"><strong>{name}</strong></div></div>
                                    <div class="table__body-box"><b class="colleague-email">{email}</b></div>
                                    <div class="table__body-box"><b class="table-status">{status}</b><i>{date}</i></div>
                                    <div class="table__body-box">

                                        <div class="dropdown-actions-view {isright}">
                                            <div class="dropdown-toggle">
                                                <span class="isorcan"><?= Yii::t('app/common', 'Is') ?></span>
                                                <span id="colleague-list-user-access-type-{colleague_id}" class="access-name" data-action="{access_type}">{access_type_name}</span>
                                            </div>
                                        </div>

                                        <div class="dropdown-actions dropdown {canright}">
                                            <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('app/common', 'Can') ?><span id="collaborate-user-access-type-{colleague_id}" data-action="{access_type}">{access_type_name}</span></div>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript:void(0)" class="ch-user-collaborate-access" data-tokens="{colleague_id}" data-action="<?= UserColleagues::PERMISSION_EDIT ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_EDIT) ?></a></li>
                                                <li><a href="javascript:void(0)" class="ch-user-collaborate-access" data-tokens="{colleague_id}" data-action="<?= UserColleagues::PERMISSION_VIEW ?>" data-subtext="<?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?>"><?= Yii::t('app/common', 'Can') ?> <?= UserColleagues::permissionLabel(UserColleagues::PERMISSION_VIEW) ?></a></li>
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="table__body-box"><a class="table-delete ch-user-collaborate-access {hideforowner}" href="javascript:void(0)" data-tokens="{colleague_id}" data-action="delete"  data-subtext="Delete">Delete</a></div>
                                </div>
                            </div>

                            <div class="modal-settCont__box">
                                <?= Html::submitButton(Yii::t('user/filemanager', 'Invite'), ['class' => "btn-big", 'id' => 'button-invite-email', 'name' => "invite-email-button"]) ?>
                            </div>

                        </div>

                    </div>


                    <div class="table table--settingsPopUp" id="invite-message-form">

                        <div class="table__body-cont" id="invite-message">
                            <span id="invite-title-message" class="modal-title modal-title-indenting3"><?= Yii::t('user/filemanager', 'Message') ?></span>
                            <textarea placeholder="<?= Yii::t('user/filemanager', 'Add_your_message_here') ?>" id="colleague-message"></textarea>
                        </div>

                        <div id="waiting-form-on-add" class="small-loading waiting-form-on-add"><?= Yii::t('user/filemanager', 'Loading') ?> <img class="loading" src="/themes/orange/images/loading_v4.gif" alt="loading..." /></div>

                    </div>

                    <div class="table table--settingsPopUp" id="colleagues-list-form">

                        <span class="modal-title modal-title-indenting2"><?= Yii::t('user/filemanager', 'Colleagues_list') ?></span>

                        <div class="table__head-cont">

                            <div class="table__head">
                                <div class="table__head-box"></div>
                                <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'User') ?></span></div>
                                <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Status') ?></span></div>
                                <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Permission') ?></span></div>
                                <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Action') ?></span></div>
                            </div>

                        </div>


                        <div class="scrollbar-program">

                            <div class="table__body-cont" id="colleagues-list">

                                <!--
                                <div class="table__body">
                                    <div class="table__body-box"><div class="userId color-yellow"><strong>ge</strong></div></div>
                                    <div class="table__body-box"><b>georgelarson@gmail.com</b></div>
                                    <div class="table__body-box"><b class="table-status">Invited</b><i>12/10/2016  10:24:19</i></div>
                                    <div class="table__body-box">
                                        <div class="dropdown-actions dropdown">
                                            <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('user/filemanager', 'Can') ?><span>View</span></div>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript:void(0)"><?= Yii::t('app/common', 'Can') ?> Edit</a></li>
                                                <li><a href="javascript:void(0)"><?= Yii::t('app/common', 'Can') ?> View</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="table__body-box"><a class="table-delete" href="javascript:void(0)">Delete</a></div>
                                </div>
                                -->

                            </div>

                        </div>

                    </div>


                    <?php
                    ActiveForm::end();
                    ?>

                    <div class="form-block" style="padding-top: 5px;">
                        <?= Html::button(Yii::t('user/filemanager', 'Cancel_collaboration'), [
                            'class' => "btn-big cancel-collaboration btn-notActive",
                            'name' => "cancel-collaboration-button",
                            'id' => "btn-cancel-collaboration",
                        ]) ?>
                    </div>

                </div>

            </div>

        </div>

    </div>
<!-- END .Modal #collaborate-modal -->


<!-- BEGIN .Modal #colleague-list-modal -->
<div class="modal modal-settings fade" id="colleague-list-modal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>

            </div>

            <div class="modal-body">

                <input type="hidden" name="leave_collaborate_filesystem_hash" id="leave-collaborate-filesystem-hash" />
                <input type="hidden" name="leave_collaborate_file_uuid" id="leave-collaborate-file-uuid" />

                <div id="colleagues_view_tpl" style="display: none">
                    <div class="table__body" id="collaborate-user-{colleague_id}">
                        <div class="table__body-box"><div class="userId color-{color}"><strong>{name}</strong></div></div>
                        <div class="table__body-box"><b class="colleague-email">{email}</b></div>
                        <div class="table__body-box"><b class="table-status">{status}</b><i>{date}</i></div>
                        <div class="table__body-box">
                            <div class="dropdown-actions-view">
                                <div class="dropdown-toggle">
                                    <span class="isorcan {show_can}"><?= Yii::t('app/common', 'Can') ?></span>
                                    <span class="isorcan {show_is}"><?= Yii::t('app/common', 'Is') ?></span>
                                    <span id="colleague-list-user-access-type-{colleague_id}" class="access-name" data-action="{access_type}">{access_type_name}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table table--settingsPopUp">

                    <span class="modal-title modal-title-indenting2"><?= Yii::t('user/filemanager', 'Colleagues_list_on_folder') ?> <p id="colleague-list-file-name">file-name</p></span>

                    <div class="table__head-cont">

                        <div class="table__head">
                            <div class="table__head-box"></div>
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'User') ?></span></div>
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Status') ?></span></div>
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Permission') ?></span></div>
                        </div>

                    </div>

                    <div class="scrollbar-program">

                        <div class="table__body-cont" id="colleagues-list-view">

                            <!--
                                <div class="table__body">
                                    <div class="table__body-box"><div class="userId color-yellow"><strong>ge</strong></div></div>
                                    <div class="table__body-box"><b>georgelarson@gmail.com</b></div>
                                    <div class="table__body-box"><b class="table-status">Invited</b><i>12/10/2016  10:24:19</i></div>
                                    <div class="table__body-box">
                                        <div class="dropdown-actions dropdown">
                                            <div class="dropdown-toggle" data-toggle="dropdown"><?= Yii::t('user/filemanager', 'Can') ?><span>View</span></div>
                                        </div>
                                    </div>
                                </div>
                                -->

                        </div>

                    </div>

                </div>

                <div class="form-block" style="padding-top: 5px;">
                    <?= Html::button(Yii::t('user/filemanager', 'Leave_collaboration'), ['class' => "btn-big leave-collaboration", 'name' => "leave-collaboration-button"]) ?>
                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .Modal #colleague-list-modal -->


<!-- BEGIN .Modal #fileversions-modal -->
<div class="modal modal-settings_ fade" id="fileversions-modal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>

            </div>

            <div class="modal-body">

                <?php
                $form = ActiveForm::begin([
                    'id' => 'fileversions-form',
                    'enableClientValidation' => true,
                    'options' => [
                        'onsubmit' => 'return false',
                    ],
                ]);
                ?>
                <div class="form-block">
                    <input type="hidden" name="fileversions_filesystem_hash" id="fileversions-filesystem-hash" />
                    <input type="hidden" name="fileversions_file_id" id="fileversions-file-id" />
                    <input type="hidden" name="fileversions_file_uuid" id="fileversions-file-uuid" />

                </div>

                <div id="version_tpl" style="display: none">
                    <div class="table__body" id="event-{event_id}">
                        <div class="table__body-box"><b class="colleague-email">{event_timestamp}</b></div>
                        <div class="table__body-box"><b class="colleague-email">{file_size_after_event}</b></div>
                        <div class="table__body-box"><b class="colleague-email">{event_type} by {user_email}</b></div>
                        <div class="table__body-box">
                            <a class="restore-patch {disabled}" href="javascript:void(0)" data-restore-status="{status}" data-event-id="{event_id}">
                                <span class="event-restore table-color-darkRed {disabled}"  style="display: {show_restore};"><?= Yii::t('user/filemanager', 'Restore') ?></span>
                                <span class="event-current table-color-darkBlue {disabled}" style="display: {show_current}; cursor: default;"><?= Yii::t('user/filemanager', 'Current') ?></span>
                                <span class="event-restored table-color-darkRed {disabled}" style="display: {show_restored};"><?= Yii::t('user/filemanager', 'Restored') ?><br /><span style="font-size: 8px;">({date_restored})</span></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table table--fileversionsPopUp" id="fileversions-list-form">

                    <span class="modal-title modal-title-indenting2"><?= Yii::t('user/filemanager', 'Patch_versions_rollback') ?> <p id="fileversions-file-name">file-name</p></span>

                    <div class="table__head-cont">

                        <div class="table__head">
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Date') ?></span></div>
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Size') ?></span></div>
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Info') ?></span></div>
                            <div class="table__head-box"><span><?= Yii::t('user/filemanager', 'Action') ?></span></div>
                        </div>

                    </div>

                    <div class="scrollbar-program">

                        <div class="table__body-cont" id="fileversions-list">


                        </div>

                    </div>

                </div>
                <?php
                ActiveForm::end();
                ?>


            </div>

        </div>

    </div>

</div>
<!-- END .Modal #fileversions-modal -->

<!-- BEGIN .Modal #download-dialog-tpl -->
<div id="download-dialog-tpl" style="display: none;">
    <div id="download-iframe" style="width: 1px; height: 1px; display: none;">
        <!--<iframe name="download_frame" src="" title="download-iframe"></iframe>-->
    </div>
    <div id="download-dialog" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable std42-dialog  elfinder-dialog elfinder-dialog-notify elfinder-dialog-active ui-front" style="width: 280px; height: auto; top: 12px; right: 12px; display: block; z-index: 1000;">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <!--
            <a href="#" class="ui-dialog-titlebar-min ui-corner-all"><span class="ui-icon-- ui-icon-min"></span></a>
            &nbsp;
            -->
            <a href="#" class="ui-dialog-titlebar-close3 ui-corner-all"><span class="ui-icon-- ui-icon-close-- ui-icon-close-download"></span></a>
        </div>

        <div class="prepare-download-text">
            <?= Yii::t('user/filemanager', 'Prepare_for_download') ?>
        </div>

        <div id="download-total-progress" class="progress -progress-upload progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="progress-bar progress-bar-success" style="width: 100%;" data-dz-totaluploadprogress=""></div>
        </div>

    </div>
</div>
<!-- END .Modal #download-dialog-tpl -->

<!-- BEGIN .Modal #download-dialog-rtc-tpl -->
<div id="download-dialog-rtc-tpl" style="display: none;">
    <div id="download-dialog-rtc" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable std42-dialog  elfinder-dialog elfinder-dialog-notify elfinder-dialog-active ui-front" style="width: 280px; height: auto; top: 12px; right: 12px; display: block; z-index: 1000;">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <!--
            <a href="#" class="ui-dialog-titlebar-min ui-corner-all"><span class="ui-icon-- ui-icon-min"></span></a>
            &nbsp;
            -->
            <a href="#" class="ui-dialog-titlebar-close4 ui-corner-all"><span class="ui-icon-- ui-icon-close"></span></a>
        </div>

        <div id="total-progress-download-rtc" class="progress -progress-upload progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="position: relative; height: 18px !important;">
            <div id="total-download-sys-info"
                 style="display: none;"
                 data-total-size="0"
                 data-total-downloaded-size="0"
                 data-total-speed="0">
            </div>
            <div id="total-download-file-info" class="file-info-total" style="width: 100%; position: absolute; top: 0; left: 0; text-align: center; height: 17px; font-size: 11px; color: #666666; font-weight: 700;">total-download-file-info</div>
            <div id="total-download-file-percent" class="progress-bar progress-bar-success" style="width:0%;"></div>
        </div>

        <div class="ui-dialog-content ui-widget-content" id="preview_downloads-rtc">



        </div>
    </div>
</div>

<div id="download-dialog-rtc-row-tpl" style="display: none;">
    <div id="download-task-{last_event_uuid}" data-event-uuid="{last_event_uuid}" data-file-size="{file_size}" data-file-downloaded="0" data-file-speed="0" class="elfinder-notify elfinder-notify-open file-row">
        <div class="file-info">
            <span class="elfinder-notify-msg name">{file_name}</span>
        </div>
        <div class="progress progress-upload progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="position: relative; height: 18px !important; margin-bottom: 0px;">
            <div id="download-file-info-{last_event_uuid}" style="width: 100%; position: absolute; top: 0; left: 0; text-align: center; height: 17px; font-size: 11px; color: #666666; font-weight: 700;">{bytesSent} / {bytesTotal}, 0bps</div>
            <div id="download-file-percent-{last_event_uuid}" class="progress-bar progress-bar-success" style="width: 0%;"></div>
        </div>

        <button class="-btn -btn-warning btn-pauseRTCDownload btn-pause-rtc-download pause" data-event-uuid="{last_event_uuid}" style="margin-top: 7px;">
            <i class="glyphicon glyphicon-pause"></i>
            <span></span>
        </button>
        <button class="-btn -btn-warning btn-cancelRTCDownload btn-cancel-rtc-download cancel" data-event-uuid="{last_event_uuid}" style="margin-top: 7px;">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span></span>
        </button>
    </div>
</div>
<!-- BEGIN .Modal #download-dialog-rtc-tpl -->

<!-- BEGIN .Modal #file-upload-modal -->
<div id="upload-dialog-tpl" style="display: none;">
    <div id="upload-dialog" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable std42-dialog  elfinder-dialog elfinder-dialog-notify elfinder-dialog-active ui-front" style="width: 280px; height: auto; top: 12px; right: 12px; display: block; z-index: 1000;">
        <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
            <a href="#" class="ui-dialog-titlebar-min ui-corner-all"><span class="ui-icon-- ui-icon-min"></span></a>
            &nbsp;
            <a href="#" class="ui-dialog-titlebar-close2 ui-corner-all"><span class="ui-icon-- ui-icon-close"></span></a>
        </div>

        <div id="total-progress" class="progress -progress-upload progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
             style="position: relative; height: 18px !important;">
            <div class="file-info-total" style="width: 100%; position: absolute; top: 0; left: 0; text-align: center; height: 17px; font-size: 11px; color: #666666; font-weight: 700;">file-info</div>
            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-totaluploadprogress=""></div>
        </div>

        <div class="ui-dialog-content ui-widget-content" id="preview_uploads">

        </div>
    </div>
</div>
<!-- END .Modal #file-upload-modal -->

<!-- HTML heavily inspired by http://blueimp.github.io/jQuery-File-Upload/ -->
<div style="display: none;">
    <div id="template_file_upload_tr" class="elfinder-notify elfinder-notify-open file-row">
        <div class="file-info">
            <span class="elfinder-notify-msg name" data-dz-name="">file_name</span>
            <span class="error text-danger" data-dz-errormessage></span>

            <!--
            <br />
            <span class="elfinder-notify-msg size" data-dz-uploaded-info-="">file_size</span>
            -->
        </div>
        <div class="progress progress-upload progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
             style="position: relative; height: 18px !important; margin-bottom: 0px;">
            <div data-dz-size="" style="width: 100%; position: absolute; top: 0; left: 0; text-align: center; height: 17px; font-size: 11px; color: #666666; font-weight: 700;">file_size</div>
            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress=""></div>
        </div>

        <button class="-btn -btn-warning btn-cancelUpload btn-cancel-upload cancel" data-dz-remove style="margin-top: 7px;">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span><?= Yii::t('app/common', 'Cancel') ?></span>
        </button>
    </div>
</div>
<!-- END .Modal #file-upload-modal -->


<!--
<div id="template" class="file-row">
    <div>
        <span class="preview"><img data-dz-thumbnail alt="thumb" /></span>
    </div>
    <div>
        <p class="name" data-dz-name></p>
        <strong class="error text-danger" data-dz-errormessage></strong>
    </div>
    <div>
        <p class="size" data-dz-size></p>
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
        </div>
    </div>
    <div>
        <button class="btn btn-primary start">
            <i class="glyphicon glyphicon-upload"></i>
            <span>Start</span>
        </button>
        <button data-dz-remove class="btn btn-warning cancel">
            <i class="glyphicon glyphicon-ban-circle"></i>
            <span><?= Yii::t('app/common', 'Cancel') ?></span>
        </button>
        <button data-dz-remove class="btn btn-danger delete">
            <i class="glyphicon glyphicon-trash"></i>
            <span><?= Yii::t('app/common', 'Delete') ?></span>
        </button>
    </div>
</div>
-->

<!-- BEGIN .Modal #collaborate-modal -->
<div class="modal modal-settings fade" id="preview-modal" tabindex="-1" role="dialog">

    <div id="preview-container" class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header" style="position: relative;">

                <span id="preview-file-name">file-name</span>
                <div type="button" class="close" data-dismiss="modal" aria-label="Close"></div>

            </div>

            <div class="modal-body">

                <div class="table table--settingsPopUp" id="preview-body">



                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .Modal #collaborate-modal -->
