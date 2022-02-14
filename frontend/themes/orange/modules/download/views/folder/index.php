<?php
/* @var $this yii\web\View */
/* @var $share_group_hash string */
/* @var $shareParent \common\models\UserFiles */
/* @var $dataProvider yii\data\ArrayDataProvider */

use yii\helpers\Html;
use yii\widgets\ListView;
use common\models\UserFiles;
use common\helpers\Functions;
use common\helpers\FileSys;
use frontend\assets\orange\modFolderDownloadAsset;

$this->title = Yii::t('modules/download', 'title_folder', ['file_name' => $shareParent->file_name]);

//$this->params['breadcrumbs'][] = ['label' => 'Главная', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
//var_dump($dataProvider->allModels); exit;
//var_dump($shareParent->file_parent_id);

modFolderDownloadAsset::register($this);
//var_dump($dataProvider);exit;
?>

<div class="features">

    <div class="features__cont">

        <div class="title"><h2><?= Yii::t('modules/download', 'Shared_folder') ?></h2></div>

        <div class="shared-folder-view">

            <div class="row">
                <div class="col-sm-10">
                    <span class="glyphicon glyphicon-folder-open folder-download-top-name">
                        &nbsp;<?= $shareParent->file_name; ?>

                    </span>
                    <table class="table table-striped table-bordered-" id="file-list-table" style="width: 100%; display: inline-table !important;">
                        <thead>
                            <th style="width: 80%;"><?= Yii::t('modules/download', 'td_file_name') ?></th>
                            <th style="width: 20%;"><?= Yii::t('modules/download', 'td_file_size') ?></th>
                        </thead>
                        <tbody>
                            <?php
                            if (sizeof($dataProvider->allModels)) {
                                //var_dump($shareParent->file_parent_id);
                                echo ListView::widget([
                                    'dataProvider' => $dataProvider,
                                    'itemOptions' => ['class' => 'item'],
                                    'layout' => "{items}",
                                    //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
                                    //'summary' => 'Показано {count} из {totalCount}',
                                    'itemView' => function ($model, $key, $index, $widget) use ($shareParent) {
                                        //$model['share_name'] = FileSys::basename($model['share_path']);
                                        if ($model['is_folder'] == UserFiles::TYPE_FOLDER) {
                                            $link = Html::a(
                                                '<span class="file file-catalog">' . $model['file_name']. '</span>',
                                                ["/folder/" . $model['share_group_hash'] . "/" . ($model['file_id'])]
                                            );
                                        } elseif ($model['is_folder'] == UserFiles::TYPE_TOP_FOLDER) {
                                            $link = Html::a(
                                                '<span class="file file-catalog">' . $model['file_name'] . '</span>',
                                                ["/folder/" . $model['share_group_hash']]
                                            );
                                            $link = false;
                                        } elseif ($model['is_folder'] == UserFiles::TYPE_UP_FOLDER) {
                                            $link = Html::a(
                                                '<span class="file file-catalog">' . $model['file_name'] . '</span>',
                                                ["/folder/" . $model['share_group_hash'] . "/" . $shareParent->file_parent_id]
                                            );
                                        } else {
                                            $mime = "";
                                            //$mime = UserFiles::fileMime($model['file_name']);
                                            $mime = FileSys::file_mime($model['file_name']);
                                            $class = "elfinder-cwd-icon-" . str_replace("/", " elfinder-cwd-icon-", $mime);

                                            $link = Html::a(
                                                '<span class="elfinder-cwd-icon-folder-download '. $class . '">' . $model['file_name'] . '</span>',
                                                ["/file/" . $model['share_hash']]
                                            );
                                        }
                                        if ($link) {
                                            return '<tr data-key="0">
                                                        <td width="80%" nowrap="nowrap" class="file-list-table-td-name">' . $link . '</td>
                                                        <td>' .
                                                            (
                                                                ($model['is_folder'] == UserFiles::TYPE_FILE)
                                                                    ? Functions::file_size_format($model['file_size'], 0)
                                                                    : '-'
                                                            ) .
                                                        '</td>
                                                    </tr>';
                                        } else {
                                            return '';
                                        }
                                    },
                                ]);
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                var download_by_app = function() {
                    var a = document.getElementById("download_by_app");
                    if (a.click === undefined)
                    {
                        window.location.href = href
                    }
                    else
                    {
                        a.click();
                    }
                }
            </script>

            <div>
                <br>
                <button id="btn_download" class="btn-default" onclick="download_by_app();" > <?= Yii::t('modules/download', 'Download_folder_by_app') ?> </button>
                <a id="download_by_app" hidden="true" href="pvtbox://folder/<?= $shareParent->share_hash ?>"></a>
            </div>

        </div>

    </div>

</div>
