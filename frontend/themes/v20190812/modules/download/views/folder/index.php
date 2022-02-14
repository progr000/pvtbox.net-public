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
use frontend\assets\v20190812\modFolderDownloadAsset;

$this->title = Yii::t('modules/download', 'title_folder', ['file_name' => $shareParent->file_name]);

modFolderDownloadAsset::register($this);
?>

<div class="content container filemanager">


    <h2><?= Yii::t('modules/download', 'Shared_folder') ?> <span class="shared-folder-name-title masterTooltip" title="<?= $shareParent->file_name ?>"><?= $shareParent->file_name ?></span></h2>


    <table class="shared-folder" id="file-list-table">
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
                                        <td nowrap="nowrap" class="file-list-table-td-name">' . $link . '</td>
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


    <div>
        <a id="download_by_app" class="btn primary-btn wide-btn" href="pvtbox://folder/<?= $shareParent->share_hash ?>"><?= Yii::t('modules/download', 'Download_folder_by_app') ?></a>
    </div>




</div>
