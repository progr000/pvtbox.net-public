<?php
/* @var $this yii\web\View */
/* @var $ServerLicensesSearchModel */
/* @var $dataProviderServerLicenses */
/* @var $admin \common\models\Users */
/* @var $server_license_count_info array */

use yii\web\View;
use yii\widgets\Pjax;
use yii\widgets\ListView;
use kartik\form\ActiveForm;

?>

<div class="inputForm admin-panel-inputForm" style="margin-top: 50px;">

    <div class="inputForm__cont">

    </div>

</div>

<div class="table-text-top">
    <span><?= Yii::t('user/admin-panel', 'Count_of_Total_server_lic_used', [
            'count' => $server_license_count_info['used'],
            'total' => $server_license_count_info['total'],
        ]) ?> <?= ""/*Html::a(Yii::t('user/admin-panel', 'Add_more'), ['purchase/add-licenses', 'billed' => Licenses::getBilledByPeriod($admin->license_period)])*/ ?>
        <a href="#" class="masterTooltip" title="<?= Yii::t("user/billing", "Contact_support") ?>"><?= Yii::t('user/admin-panel', 'Add_more') ?></a>
    </span>
</div>

<div class="table table--server-devices">

    <div class="table__head-cont" style="padding-right: 0px;">

        <div class="table__head">
            <div class="table__head-box"><span>&nbsp; &nbsp;</span></div>
            <div class="table__head-box"><span><?= Yii::t('user/devices', 'Device_type') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/devices', 'Operating_system') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/devices', 'Name') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/devices', 'User') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/devices', 'Licensed') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/devices', 'Action') ?></span></div>
        </div>

    </div>

    <?php Pjax::begin(['id' => 'server-license-list-content']); ?>
    <?php
    $minPageSize = 7;
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
        'layout' => '<div class="scrollbar-box"><div class="table__body-cont">' . "{items}" . '</div></div>' . "\n{pager}",
        'emptyText' => $this->render('index_ServerLicenses_ListItemNoData'),
        'emptyTextOptions' => ['tag' => false],
        //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
        //'summary' => 'Показано {count} из {totalCount}',
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

    <!--
    <div class="inform-boxBottom">
        <div class="inform inform-inlineMin"><p>* Inform box.</p></div>
    </div>
    -->

</div>

