<?php
/* @var $admin \common\models\Users */
?>

<div class="scrollbar-box">
    <div class="table__body-cont">

        <div class="table__body">
            <div class="table__body-box"><div class="userId color-<?= $admin->_color ?>"><strong><?= $admin->_sname ?></strong></div></div>
            <div class="table__body-box"><b><?= $admin->user_email ?></b></div>
            <div class="table__body-box"><span class="table-status">Registered</span><i><?= date(Yii::$app->params['datetime_format'], strtotime($admin->user_created)) ?></i></div>
            <div class="table__body-box"><b class="table-color-dark manage-colleague-folder- masterTooltip admin-of-panel" title="You are the Admin">All</b></div>
            <div class="table__body-box"></div>
        </div>
        <div class="table__body">
            <div class="table__body-box"><div class="userId"><strong></strong></div></div>
            <div class="table__body-box"><b></b></div>
            <div class="table__body-box"><span></span><i></i></div>
            <div class="table__body-box"><b></b><b></b></div>
            <div class="table__body-box"><span class="table-color-gray"></span></div>
        </div>
        <div class="table__body">
            <div class="table__body-box"><div class="userId"><strong></strong></div></div>
            <div class="table__body-box"><b></b></div>
            <div class="table__body-box"><span></span><i></i></div>
            <div class="table__body-box"><b></b><b></b></div>
            <div class="table__body-box"><span class="table-color-gray"></span></div>
        </div>
        <div class="table__body">
            <div class="table__body-box"><div class="userId"><strong></strong></div></div>
            <div class="table__body-box"><b></b></div>
            <div class="table__body-box"><span></span><i></i></div>
            <div class="table__body-box"><b></b><b></b></div>
            <div class="table__body-box"><span class="table-color-gray"></span></div>
        </div>
        <div class="table__body">
            <div class="table__body-box"><div class="userId"><strong></strong></div></div>
            <div class="table__body-box"><b></b></div>
            <div class="table__body-box"><span></span><i></i></div>
            <div class="table__body-box"><b></b><b></b></div>
            <div class="table__body-box"><span class="table-color-gray"></span></div>
        </div>
        <div class="table__body">
            <div class="table__body-box"><div class="userId"><strong></strong></div></div>
            <div class="table__body-box"><b></b></div>
            <div class="table__body-box"><span></span><i></i></div>
            <div class="table__body-box"><b></b><b></b></div>
            <div class="table__body-box"><span class="table-color-gray"></span></div>
        </div>
        <div class="table__body">
            <div class="table__body-box"><div class="userId"><strong></strong></div></div>
            <div class="table__body-box"><b></b></div>
            <div class="table__body-box"><span></span><i></i></div>
            <div class="table__body-box"><b></b><b></b></div>
            <div class="table__body-box"><span class="table-color-gray"></span></div>
        </div>

    </div>
</div>