<table class="devices-tbl">
    <thead>
    <tr>
        <th></th>
        <th><?= Yii::t('user/devices', 'Device_type') ?></th>
        <th><?= Yii::t('user/devices', 'Operating_system') ?></th>
        <th><?= Yii::t('user/devices', 'Name') ?></th>
        <th><?= Yii::t('user/devices', 'In_use') ?></th>
        <th><?= Yii::t('user/devices', 'Status') ?></th>
        <th><?= Yii::t('user/devices', 'Current_speed') ?></th>
        <th><?= Yii::t('user/devices', 'Action') ?></th>
    </tr>
    </thead>
    <tbody id="list-items-node" style="display: none;">
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8"><?= Yii::t('app/common', 'No_records_found') ?></td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
        <tr class="item-node item-node-empty" data-node-status="100">
            <td colspan="8">&nbsp;</td>
        </tr>
    </tbody>
</table>
<span class="devices-note-gray">* Actual content + File versions</span>