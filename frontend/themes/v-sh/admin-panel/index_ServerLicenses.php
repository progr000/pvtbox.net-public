<?php
/* @var $this yii\web\View */
/* @var $ServerLicensesSearchModel */
/* @var $dataProviderServerLicenses */
/* @var $admin \common\models\Users */
/* @var $server_license_count_info array */

use yii\widgets\Pjax;
use yii\widgets\ListView;

?>
<div class="form-title form-title--row">
    <div class="licences-control"><span><?= Yii::t('user/admin-panel', 'Count_of_Total_server_lic_used', [
                'count' => $server_license_count_info['used'],
                'total' => $server_license_count_info['total'],
            ]) ?></span><a class="masterTooltip void-0" href="#" title="<?= Yii::t("user/billing", "Contact_support") ?>"><?= Yii::t('user/admin-panel', 'Add_more') ?></a></div>
</div>
<div class="table-wrap">
    <div class="table-wrap__inner">

        <?php Pjax::begin(['id' => 'server-license-list-content']); ?>
        <?php
        $minPageSize = 8;
        $count = $dataProviderServerLicenses->count;
        $lost = isset($dataProviderServerLicenses->pagination->pageSize) ? $dataProviderServerLicenses->pagination->pageSize - $count : $minPageSize - $count;
        ?>
        <?=
        ListView::widget([
            'dataProvider' => $dataProviderServerLicenses,
            //'itemOptions' => ['class' => 'item'],
            'itemOptions' => [
                'tag' => false,
                'class' => '',
            ],
            'layout' => '
                <table class="server-tbl">
                    <thead>
                        <tr>
                            <th></th>
                            <th>' . Yii::t('user/devices', 'Device_type') . '</th>
                            <th>' . Yii::t('user/devices', 'Operating_system') . '</th>
                            <th>' . Yii::t('user/devices', 'Name') . '</th>
                            <th>' . Yii::t('user/devices', 'User') . '</th>
                            <th>' . Yii::t('user/devices', 'Licensed') . '</th>
                            <th>' . Yii::t('user/devices', 'Action') . '</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items}
                    </tbody>
                </table>
                {pager}
                ',
            'emptyText' => $this->render('index_ServerLicenses_ListItemNoData'),
            'emptyTextOptions' => ['tag' => false],
            'itemView' => function ($model, $key, $index, $widget) use ($lost, $count) {
                $lost_row = '';
                if ($lost>0 && ($index == $count - 1)) {
                    for ($i=1; $i<=$lost; $i++) {
                        $lost_row .= $this->render('index_ServerLicenses_ListItemEmpty');
                    }
                }
                /** @var $model \frontend\models\search\UserNodeSearch */
                return $this->render('index_ServerLicenses_ListItem', ['model' => $model]) . $lost_row;
            },
        ]);
        ?>
        <?php Pjax::end(); ?>

    </div>
</div>

<?php if (false) { ?>


    <tr>
        <td>
            <div class="lock active">
                <svg class="icon icon-lock">
                    <use xlink:href="#lock"></use>
                </svg>
            </div>
        </td>
        <td>
            <div class="browser">
                <svg class="icon icon-earth">
                    <use xlink:href="#earth"></use>
                </svg><span>Browser</span>
            </div>
        </td>
        <td>
            <div class="system">
                <svg class="icon icon-system-windows">
                    <use xlink:href="#system-windows"></use>
                </svg><span>Windows 7</span>
            </div>
        </td>
        <td><span>Chrome 75.0.3770.142</span></td>
        <td>rebroff1@gmail.com</td>
        <td>20.12.2020</td>
        <td></td>
    </tr>
    <tr>
        <td>
            <div class="lock">
                <svg class="icon icon-lock">
                    <use xlink:href="#lock"></use>
                </svg>
            </div>
        </td>
        <td>
            <div class="browser">
                <svg class="icon icon-earth">
                    <use xlink:href="#earth"></use>
                </svg><span>Browser</span>
            </div>
        </td>
        <td>
            <div class="system">
                <svg class="icon icon-system-android">
                    <use xlink:href="#system-android"></use>
                </svg><span>Android 6.0</span>
            </div>
        </td>
        <td><span>Chrome 75.0.3770.142</span></td>
        <td>rebroff1@gmail.com</td>
        <td>20.12.2020</td>
        <td></td>
    </tr>
    <tr>
        <td>
            <div class="lock">
                <svg class="icon icon-lock">
                    <use xlink:href="#lock"></use>
                </svg>
            </div>
        </td>
        <td>
            <div class="browser">
                <svg class="icon icon-earth">
                    <use xlink:href="#earth"></use>
                </svg><span>Browser</span>
            </div>
        </td>
        <td>
            <div class="system">
                <svg class="icon icon-system-apple">
                    <use xlink:href="#system-apple"></use>
                </svg><span>macOS 10.15</span>
            </div>
        </td>
        <td><span>Safari 12.1.1</span></td>
        <td>rebroff1@gmail.com</td>
        <td>20.12.2020</td>
        <td></td>
    </tr>


<?php } ?>