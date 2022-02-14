<!-- begin buttons-template -->
<div id="buttons-template" style="display: none;">

    <a id="online_office_tpl_link" target="_blank" href="#">Open in Online Office</a>

    <!-- #tpl-share-link-and-colleague-list -->
    <div id="tpl-share-link-and-colleague-list">
        <div class="workspace-sub__box">
            <div class="dropdown-share dropdown" id="shareDropMenu_{hash}">
                <div class="dropdown-toggle" data-toggle="dropdown" id="buttonDropMenu_{hash}" onclick="showShareDropMenu('{hash}')"><?= Yii::t('user/filemanager', 'Share') ?></div>
                <ul class="dropdown-menu">
                    <li><span class="share-collaborate-exec" ontouchstart="exec_share('{hash}', event)" onclick="exec_share('{hash}', event)"><?= Yii::t('user/filemanager', 'Get_link') ?></span></li>
                    <li><span class="share-collaborate-exec" ontouchstart="showColleagueList('{hash}', event)" onclick="showColleagueList('{hash}', event)"><?= Yii::t('user/filemanager', 'Colleague_list') ?></span></li>
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
                    <li><span class="share-collaborate-exec" ontouchstart="exec_share('{hash}', event)" onclick="exec_share('{hash}', event)"><?= Yii::t('user/filemanager', 'Get_link') ?></span></li>
                    <li><span class="share-collaborate-exec" ontouchstart="showCollaborationDialog('{hash}', event)" onclick="showCollaborationDialog('{hash}', event)"><?= Yii::t('user/filemanager', 'Collaboration_Settings') ?></span></li>
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
<!-- end buttons-template -->