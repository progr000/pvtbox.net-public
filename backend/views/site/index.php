<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use backend\models\Admins;
use common\helpers\Functions;
use common\models\Preferences;
use common\models\Users;
use common\models\UserNode;
use common\models\Licenses;

/** @var $this yii\web\View */
/** @var $dataMailqTotal array */
/** @var $dataTestsLogList yii\data\ArrayDataProvider */
/** @var $totalUsersInfo array */
/** @var $totalUserNodeInfo array */
/** @var $Admin \backend\models\Admins */
/** @var $PhpLogs array */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <div class="body-content">

        <div class="row">

            <div class="col-lg-6">
                <h2>Registration Statistics:</h2>
                <p>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProviderUsers,
                        //'filterModel' => $searchModelUsers,
                        'summary' => false,
                        'showHeader' => false,

                        'columns' => [
                            [
                                'attribute' => 'period',
                                'value' => function ($data) {
                                    return Functions::forPeriod($data['period']);
                                },
                            ],
                            [
                                'attribute' => 'cnt',
                                'format' => 'raw',
                                'value' => function($data) {
                                    return '<a href="/users/index?sort=-user_created">' . $data['cnt'] . '</a>';
                                },
                            ],
                        ],
                    ]);
                    ?>
                </p>
            </div><!-- col-lg-6 (Registration Statistics) -->

            <div class="col-lg-6">
                <h2>Payment statistics:</h2>
                <p>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProviderPayments,
                        //'filterModel' => $searchModel,
                        'summary' => false,
                        'showHeader' => false,

                        'columns' => [
                            [
                                'attribute' => 'period',
                                'value' => function ($data) {
                                    return Functions::forPeriod($data['period']);
                                },
                            ],
                            [
                                'attribute' => 'cnt',
                                'format' => 'raw',
                                'value' => function($data) {
                                    return '<a href="/payments/index?sort=-pay_date">' . $data['cnt'] . '</a>';
                                },
                            ],
                        ],
                    ]);
                    ?>
                </p>
            </div><!-- col-lg-6 (Payment statistics) -->

        </div><!-- row -->

        <div class="row">

            <div class="col-lg-6">
                <h2>Statistics on user activity for the period:</h2>
                <p>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProviderActivity,
                        //'filterModel' => $searchModelUsers,
                        'summary' => false,
                        'showHeader' => false,

                        'columns' => [
                            [
                                'attribute' => 'period',
                                'value' => function ($data) {
                                    return Functions::forPeriod($data['period']);
                                },
                            ],
                            [
                                'attribute' => 'cnt',
                                'format' => 'raw',
                                'value' => function($data) {
                                    return '<a href="/users/index?sort=-user_updated">' . $data['cnt'] . '</a>';
                                },
                            ],
                        ],
                    ]);
                    ?>
                </p>
            </div><!-- col-lg-6 (Statistics on user activity for the period) -->

            <div class="col-lg-6">
                <h2>Statistics on user Shares and Collaborations:</h2>
                <p></p>
                <div id="w3" class="grid-view">
                    <table class="table table-striped table-bordered">
                        <tbody>
                        <?php
                        /** @var array $dataProviderSAC */
                        foreach ($dataProviderSAC as $k=>$v) {
                            $prevValue = Preferences::getValueByKey($v['pref_key'], 0, 'integer');
                            $delta = intval($v['cnt']) - $prevValue;
                            $delta_color = "inherit";
                            if ($delta > 0) { $delta = "+" . $delta; $delta_color = "#01AB33"; }
                            if ($delta < 0) { $delta_color = "#ff0000"; }

                            $link = "";
                            if ($v['pref_key'] == 'PrefHiddenTotalSharesCount') {
                                $link = "/shares/index?sort=-share_created";
                            }
                            if ($v['pref_key'] == 'PrefHiddenTotalCollaborationsCount') {
                                $link = "/collaborations/index?sort=-collaboration_created";
                            }
                            echo "<tr data-key=\"{$k}\">
                                        <td>{$v['field']}</td>
                                        <td>
                                            <a href=\"{$link}\">{$v['cnt']} <span style=\"font-weight: bold; color: {$delta_color};\">({$delta})</span></a>
                                        </td>
                                      </tr>";
                        }
                        ?>
                        <tr data-key="<?= ++$k ?>"><td colspan="2"></td></tr>
                        <tr data-key="<?= ++$k ?>">
                            <td colspan="2">
                                <form method="post" action="/site/search-share">
                                    <?= Html::hiddenInput(Yii::$app->getRequest()->csrfParam, Yii::$app->getRequest()->getCsrfToken(), []) ?>
                                    <input type="text" name="share_val" placeholder="Enter ShareLink or share_hash" style="width: 80%;" />
                                    <input type="submit" name="Find" value="Find" style="width: 19%">
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <p></p>
            </div><!-- col-lg-6 (Statistics on user Shares and Collaborations) -->

        </div><!-- row -->

        <div class="row">

            <div class="col-lg-6">
                <h2>Statistics about total users:</h2>

                <p></p>
                <div id="w3" class="grid-view">
                    <table class="table table-striped table-bordered">
                        <tbody>
                        <?php
                        foreach ($totalUsersInfo['online'] as $k=>$v) {
                            if ($k === UserNode::ONLINE_ON) {
                                $link = "/users/index?sort=-user_updated";
                            } else if ($k === UserNode::ONLINE_OFF) {
                                $link = "/users/index?sort=user_updated";
                            } else {
                                $link = "/users/index?sort=-user_created";
                            }
                            echo "<tr>
                                    <td>" . UserNode::onlineLabel($k) . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                        }
                        ?>
                        <tr><td colspan="2">&nbsp;</td></tr>
                        <?php
                        if (isset($totalUsersInfo['licenses'])) {
                            foreach ($totalUsersInfo['licenses'] as $k => $v) {
                                $link = "/users/index?UsersSearch[license_type]={$k}";
                                echo "<tr>
                                    <td>" . Licenses::getType($k) . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                            }
                        }
                        ?>
                        <tr><td colspan="2">&nbsp;</td></tr>
                        <?php
                        if (isset($totalUsersInfo['statuses'])) {
                            foreach ($totalUsersInfo['statuses'] as $k => $v) {
                                $link = "/users/index?UsersSearch[user_status]={$k}";
                                echo "<tr>
                                    <td>" . Users::statusLabel($k) . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                            }
                        }
                        ?>

                        </tbody>
                    </table>
                </div>
                <p></p>
            </div><!-- col-lg-6 (Total users info ) -->

            <div class="col-lg-6">
                <h2>Statistics about total nodes:</h2>

                <p></p>
                <div id="w3" class="grid-view">
                    <table class="table table-striped table-bordered">
                        <tbody>
                        <?php
                        foreach ($totalUserNodeInfo['online'] as $k=>$v) {
                            if ($k === UserNode::ONLINE_ON) {
                                $link = "/user-node/index?UserNodeSearch[node_online]={$k}&sort=-node_updated";
                            } else if ($k === UserNode::ONLINE_OFF) {
                                $link = "/user-node/index?UserNodeSearch[node_online]={$k}&sort=node_updated";
                            } else if ($k === 'OnLineBrowser') {
                                $link = "/user-node/index?sort-p1=-node_updated&UserNodeSearch[node_devicetype]=" . UserNode::DEVICE_BROWSER;
                            } else {
                                $link = "/user-node/index?sort=-node_created";
                            }
                            echo "<tr>
                                    <td>" . UserNode::onlineLabel($k) . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                        }
                        ?>
                        <tr><td colspan="2">&nbsp;</td></tr>
                        <?php
                        if (isset($totalUserNodeInfo['devicetype'])) {
                            foreach ($totalUserNodeInfo['devicetype'] as $k => $v) {
                                $link = "/user-node/index?UserNodeSearch[node_devicetype]={$k}";
                                echo "<tr>
                                    <td>" . UserNode::deviceLabel($k) . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                            }
                        }
                        ?>
                        <tr><td colspan="2">&nbsp;</td></tr>
                        <?php
                        if (isset($totalUserNodeInfo['statuses'])) {
                            foreach ($totalUserNodeInfo['statuses'] as $k => $v) {
                                $link = "/user-node/index?UserNodeSearch[node_status]={$k}";
                                echo "<tr>
                                    <td>" . UserNode::statusLabel($k) . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                            }
                        }
                        ?>
                        <tr><td colspan="2">&nbsp;</td></tr>
                        <?php
                        if (isset($totalUserNodeInfo['is_server'])) {
                            foreach ($totalUserNodeInfo['is_server'] as $k => $v) {
                                $link = "/user-node/index?UserNodeSearch[is_server]={$k}";
                                echo "<tr>
                                    <td>" . ($k == UserNode::IS_SERVER ? 'Is Server OS' : 'Not Server OS') . "</td>
                                    <td><a href=\"{$link}\">{$v}</a></td>
                                  </tr>";
                            }
                        }
                        ?>

                        </tbody>
                    </table>
                </div>
                <p></p>
            </div><!-- col-lg-6 (Total nodes info) -->

        </div><!-- row -->

        <?php if ($Admin->admin_role == Admins::ROLE_ROOT) { ?>
        <div class="row">

            <div class="col-lg-6">
                <h2>Sent mail total statistics:</h2>
                <p>
                <div id="w3" class="grid-view">
                    <table class="table table-striped table-bordered">
                        <tbody>
                        <tr>
                            <th colspan="2">
                                <a href="/mailq/index?sort=-mail_created">Mails:</a>
                            </th>
                        </tr>
                        <?php
                        /** @var array $dataProviderSAC */
                        foreach ($dataMailqTotal as $k=>$v) {

                            $link = "/mailq/index?sort=-mail_created&MailqSearch[mailer_letter_status]={$k}";

                            echo "<tr data-key=\"{$k}\">
                                                <td>{$k}</td>
                                                <td>
                                                    <a href=\"{$link}\">{$v}</a>
                                                </td>
                                              </tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                </p>
            </div><!-- col-lg-6 (Sent mail total statistics) -->

            <div class="col-lg-6">
                <h2>General statistics by queue:</h2>
                <p></p>
                <?php
                /** @var $QueuesStatuses array */
                if (is_array($QueuesStatuses)) {
                    $title = isset($QueuesStatuses[0]) ? $QueuesStatuses[0] : null;
                    $colspan = isset($title) ? sizeof($title) : 1;
                    ?>
                    <div id="w0" class="grid-view">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                <th colspan="<?= $colspan ?>">
                                    <a href="/queued/index?sort=-job_created">Jobs:</a>
                                </th>
                            </tr>
                            <tr>
                                <?php
                                if (is_array($title)) {
                                    foreach ($title as $k => $v) {
                                        if ($k == 'status') {
                                            $k = "";
                                            $width = "40%";
                                        } else {
                                            $width = "30%";
                                        }
                                        echo "<th width=\"{$width}\" class=\"bold_title\"><a href=\"/queued/index?sort=-job_created&QueuedEventsSearch[queue_id]={$k}\">{$k}</a></th>";
                                    }
                                }
                                ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($QueuesStatuses as $v) {
                                echo "<tr>";
                                //var_dump($v);
                                $status = $v['status'];
                                foreach ($v as $kk => $vv) {
                                    if ($kk != 'status') {
                                        $queue_id = $kk;
                                        $class = "";
                                    } else {
                                        $queue_id = '';
                                        $class = "bold_title";
                                    }
                                    echo "<td class=\"{$class}\"><a href=\"/queued/index?sort=-job_created&QueuedEventsSearch[job_status]={$status}&QueuedEventsSearch[queue_id]={$queue_id}\">{$vv}</a></td>";
                                }
                                echo "</tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                }
                ?>
                <p></p>
            </div><!-- col-lg-6 (General statistics by queue) -->

        </div><!-- row -->
        <?php } ?>

        <?php if ($Admin->admin_role == Admins::ROLE_ROOT) { ?>
        <div class="row">

            <div class="col-lg-6">
                <h2>Information about cron scripts:</h2>
                <p>
                    <?php Pjax::begin(); ?>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProviderCronInfo,
                        //'filterModel' => $searchModelUsers,
                        'summary' => false,
                        'showHeader' => true,
                        'pjax'=>true,

                        'columns' => [
                            [
                                'attribute' => 'task_name',
                                'label'     => 'Task',
                            ],
                            [
                                'attribute' => 'task_last_start',
                                'label'     => 'Last start',
                            ],[
                                'attribute' => 'task_last_finish',
                                'label'     => 'Finish',
                            ],

                            [
                                //'class' => 'yii\grid\ActionColumn',
                                'class'=>'kartik\grid\ActionColumn',
                                'width' => '1%',
                                'vAlign' => 'top',
                                'template' => '{schedule} {last-log}',
                                'buttons' => [

                                    'schedule' => function ($url, $model) {
                                        return Html::a(
                                            '<span class="glyphicon glyphicon-time"></span>',
                                            '#',
                                            [
                                                'data-pjax' => '0',
                                                'target' => '_blank',
                                                'class' => 'show-cron-task-schedule',
                                                'title' => "Preferred schedule: " . $model->task_schedule,
                                            ]
                                        );
                                    },

                                    'last-log' => function ($url, $model) {
                                        return Html::a(
                                            '<span class="glyphicon glyphicon-list-alt"></span>',
                                            '#',
                                            [
                                                'title' => 'Last log',
                                                'data-pjax' => "0",
                                                'class' => 'show-cron-task-log',
                                                'data-task-id' => $model->task_id,
                                            ]
                                        );
                                    }
                                ],
                            ],
                        ],
                    ]);
                    ?>
                    <?php Pjax::end(); ?>
                </p>
            </div><!-- col-lg-6 (Information about cron scripts) -->


            <div class="col-lg-6">
                <?php if (!Yii::$app->params['self_hosted']) { ?>
                <h2 style="display: inline-block">Information about Tests:</h2>
                <input type="button"
                       value="In progress..."
                       id="exec-test-manually"
                       class="not-Active"
                       disabled="disabled"
                       data-value-in-progress="In progress..."
                       data-value-ready-to-start="Start Manually" />
                <p>
                    <?php Pjax::begin(['id' => 'info-about-tests']); ?>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataTestsLogList,
                        //'filterModel' => $searchModelUsers,
                        'summary' => false,
                        'showHeader' => true,
                        'pjax'=>true,
                        //'layout' => "{items}\n{pager}",
                        //'layout' => "{items}",
                        //'panelBeforeTemplate' => "",
                        //'panelAfterTemplate'  => "",
                        //'panelFooterTemplate' => "{pager}",

                        'columns' => [
                            [
                                'attribute' => 'date',
                                //'label'     => 'Date',
                                'width' => '60%'
                            ],

                            [
                                'attribute' => 'type',
                                'width' => '20%'
                            ],

                            [
                                //'class' => 'yii\grid\ActionColumn',
                                'class'=>'kartik\grid\ActionColumn',
                                'width' => '1%',
                                'vAlign' => 'top',
                                'template' => '{log}',
                                'buttons' => [

                                    'log' => function ($url, $model) {
                                        return Html::a(
                                            '<span class="glyphicon glyphicon-list-alt"></span>',
                                            ['site/view-tests-log', 'report'=>$model['idx']],
                                            [
                                                'title' => 'log',
                                                'data-pjax' => "0",
                                                'target' => '_blank',
                                                //'class' => 'show-cron-task-log',
                                                'data-task-id' => $model['idx'],
                                            ]
                                        );
                                    }
                                ],
                            ],
                        ],
                    ]);
                    ?>
                    <?php Pjax::end(); ?>
                </p>
                <?php } ?>
            </div><!-- col-lg-6 (Information about Tests) -->

        </div><!-- row -->

        <div class="row">

            <div class="col-lg-6">
                <h2>Information about php-logs:</h2>
                <p>

                <div id="w3" class="grid-view">
                    <table class="table table-striped table-bordered">
                        <tbody>
                        <tr>
                            <th width="60%">
                                <a href="#" class="void-0"></a>
                            </th>
                            <th width="30%">
                                Size
                            </th>
                            <th width="10%">
                                Actions
                            </th>
                        </tr>
                        <?php
                        foreach ($PhpLogs as $k=>$v) {
                            ?>

                            <tr data-key="<?= $k ?>">
                                <td><?= $k ?></td>
                                <td>
                                    <?php
                                    if ($v) {
                                        echo \common\helpers\Functions::file_size_format($v, 2);
                                    } else {
                                        echo 'empty';
                                    }
                                    ?>
                                </td>
                                <td class="skip-export kv-align-center kv-align-top">
                                    <a href="#"
                                       title="view"
                                       class="void-0 view-php-log"
                                       data-target="<?= $k ?>"><span class="glyphicon glyphicon-list-alt"></span></a>
                                    &nbsp;&nbsp;
                                    <a href="/site/clear-php-log?target=<?= $k ?>"
                                       title="Clear"
                                       data-log-type="<?= $k ?>"
                                       aria-label="Clear"
                                       data-pjax="1"
                                       data-method="post"
                                       data-confirm="Are you sure to clear this log?"><span class="glyphicon glyphicon-trash"></span></a>
                                </td>
                            </tr>

                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                </p>
            </div><!-- col-lg-6 (Information about cron scripts) -->


            <div class="col-lg-6">

            </div><!-- col-lg-6 (Information about Tests) -->

        </div><!-- row -->

        <?php } ?>

    </div>
</div>


<!-- BEGIN .Modal #file-on-nodes-modal -->
<style>
    .pagination { margin: 0px; }
    #info-about-tests .table {
        margin-bottom: 2px;
    }
</style>
<div class="modal modal-settings fade" id="dashboard-modal" tabindex="-1" role="dialog">

    <div class="modal-dialog" role="document" style="width: 65%;">

        <div class="modal-content">

            <div class="modal-header">

                <span id="dashboard-list-caption" class="caption-modal-list">Task last log</span>
                <div type="button" class="close" data-dismiss="modal" aria-label="Close">x</div>

            </div>

            <div class="modal-body">

                <div class="table table--settingsPopUp">

                    <pre id="dashboard-modal-list">
                        Loading...
                    </pre>

                </div>

            </div>

        </div>

    </div>

</div>
<!-- END .Modal #file-on-nodes-modal -->