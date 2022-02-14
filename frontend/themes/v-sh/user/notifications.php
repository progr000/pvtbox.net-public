<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $current_count_unread_notifications integer */

use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title = Yii::t('user/notifications', 'title');

?>
<!-- begin Notifications-page content -->
<div class="content container" id="notifications-container" data-current-count-unread-notifications="<?= $current_count_unread_notifications ?>">
    <h1><?= Yii::t('app/header', 'Notifications') ?></h1>
    <div class="table-wrap">
        <?php Pjax::begin(['id' => 'notifications-list-content']); ?>
        <?php
        $count = $dataProvider->count;
        $lost = $dataProvider->pagination->pageSize - $count;
        ?>
        <?=
        ListView::widget([
            'dataProvider' => $dataProvider,
            //'itemOptions' => ['class' => 'item'],
            'itemOptions' => [
                'tag' => false,
                'class' => '',
            ],
            'layout' => '
                <div class="table-wrap__inner">
                    <table class="notify-tbl">
                        <thead>
                            <tr>
                                <th>' . Yii::t('user/notifications', 'Message') . '</th>
                                <th>' . Yii::t('user/notifications', 'Date') . '</th>
                            </tr>
                        </thead>
                        <tbody>' .
                                "{items}" . '
                        </tbody>
                    </table>
                    {pager}
                </div>',
            'emptyText' => $this->render('notifications_list_nodata'),
            'emptyTextOptions' => ['tag' => false],
            //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
            //'summary' => 'Показано {count} из {totalCount}',
            'itemView' => function ($model, $key, $index, $widget) use ($lost, $count) {
                $lost_row = '';
                if ($lost>0 && ($index == $count - 1)) {
                    for ($i=1; $i<=$lost; $i++) {
                        $lost_row .= $this->render('notifications_list_item_empty');
                    }
                }
                /* @var $model \frontend\models\search\NotificationsSearch */
                return $this->render('notifications_list_item', ['model' => $model]) . $lost_row;
            },
        ]);
        ?>
        <?php Pjax::end(); ?>
    </div>
</div>
<!-- end Notifications-page content -->

