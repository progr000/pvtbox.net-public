<?php
/** @var $field array */
?>
<table class="reports-tbl">
    <thead>
    <tr>
        <th></th>
        <th>
            <span><?= Yii::t('user/admin-panel', 'Reports_User') ?></span>
            <div class="select-wrap">
                <?=  $field['colleague_user_email'] ?>
            </div>
        </th>
        <th>
            <span><?=  Yii::t('user/admin-panel', 'Reports_Activity') ?></span>
            <div class="select-wrap">
                <?=  $field['event_type'] ?>
            </div>
        </th>
        <th>
            <span><?=  Yii::t('user/admin-panel', 'Reports_Date') ?></span>
            <div class="datepicker-wrap">
                <button class="btn datepicker-reset-btn js-datepicker-reset" type="button" title="Clear">
                    <svg class="icon icon-close">
                        <use xlink:href="#close"></use>
                    </svg>
                </button>
                <?=  $field['created_at_range'] ?>
            </div>

        </th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4" align="center"><?= Yii::t('app/common', 'No_records_found') ?></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td><br /></td>
            <td></td>
        </tr>
    </tbody>
</table>