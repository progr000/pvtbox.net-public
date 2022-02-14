<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\widgets\ListView;
use yii\widgets\Pjax;
use common\models\Notifications;

?>
<!-- .events -->
<div class="events">

    <div class="events__cont">



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
                'layout' => '<div class="events-list" id="container-for-notifications-list">' . "{items}\n{pager}" . '</div>',
                'emptyText' => $this->render('notification_list_nodata'),
                'emptyTextOptions' => ['tag' => false],
                //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
                //'summary' => 'Показано {count} из {totalCount}',
                'itemView' => function ($model, $key, $index, $widget) use ($lost, $count) {
                    $lost_row = '';
                    if ($lost>0 && ($index == $count - 1)) {
                        for ($i=1; $i<=$lost; $i++) {
                            $lost_row .= $this->render('notification_list_item_empty');
                        }
                    }
                    /* @var $model \frontend\models\search\NotificationsSearch */
                    return $this->render('notification_list_item', ['model' => $model]) . $lost_row;
                },
            ]);
            ?>
            <?php Pjax::end(); ?>




    </div>

</div>
<!-- END .events -->