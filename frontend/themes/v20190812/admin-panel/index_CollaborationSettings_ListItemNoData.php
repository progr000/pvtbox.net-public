<?php
/* @var $admin \common\models\Users */
?>
<table class="colleague-tbl">
    <thead>
        <tr>
            <th></th>
            <th><?= Yii::t('user/admin-panel', 'CollSet_User') ?></th>
            <th><?= Yii::t('user/admin-panel', 'CollSet_Status') ?></th>
            <th><?= Yii::t('user/admin-panel', 'CollSet_Permission') ?></th>
            <th><?= Yii::t('user/admin-panel', 'CollSet_Action') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><div class="user-short color-<?= $admin->_color ?>"><?= $admin->_sname ?></div></td>
            <td><?= $admin->user_email ?></td>
            <td>
                <div class="reg-info">
                    <span class="table-status"><?= Yii::t('user/admin-panel', 'Registered') ?></span>
                    <br />
                    <span class="format-date-js" data-ts="<?= $admin->user_created ?>"><?= date(Yii::$app->params['datetime_format'], strtotime($admin->user_created)) ?></span>
                </div>
            </td>
            <td><span class="masterTooltip has-tooltip admin-of-panel" title="<?= Yii::t('user/admin-panel', 'You_are_the_Admin') ?>"><?= Yii::t('user/admin-panel', 'All') ?></span></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><div class="reg-info">
                    <span class="table-status">&nbsp;</span>
                    <br />
                    <span>&nbsp;</span>
                </div></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><div class="reg-info">
                    <span class="table-status">&nbsp;</span>
                    <br />
                    <span>&nbsp;</span>
                </div></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><div class="reg-info">
                    <span class="table-status">&nbsp;</span>
                    <br />
                    <span>&nbsp;</span>
                </div></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><div class="reg-info">
                    <span class="table-status">&nbsp;</span>
                    <br />
                    <span>&nbsp;</span>
                </div></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><div class="reg-info">
                    <span class="table-status">&nbsp;</span>
                    <br />
                    <span>&nbsp;</span>
                </div></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><div class="reg-info">
                    <span class="table-status">&nbsp;</span>
                    <br />
                    <span>&nbsp;</span>
                </div></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
