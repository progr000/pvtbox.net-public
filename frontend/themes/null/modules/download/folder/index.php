<?php
/* @var $this yii\web\View */
/* @var $share_group_hash string */
/* @var $shareParent \common\models\UserFiles */
/* @var $dataProvider yii\data\ArrayDataProvider */

use yii\helpers\Html;
use yii\widgets\ListView;
use common\models\UserFiles;

$this->title = 'Папка';
//$this->params['breadcrumbs'][] = ['label' => 'Главная', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
//var_dump($dataProvider->allModels); exit;
//var_dump($shareParent->file_parent_id);
?>

<div class="shared-folder-view">

    <div class="row">
        <div class="col-sm-10">
            <span class="glyphicon glyphicon-folder-open">&nbsp;<?= $shareParent->file_name; ?></span>
            <table class="table table-striped table-bordered">
                <thead>
                    <th>Имя файла</th>
                    <th>Размер</th>
                </thead>
                <tbody>
                    <?=
                    //var_dump($shareParent->file_parent_id);
                    ListView::widget([
                        'dataProvider' => $dataProvider,
                        'itemOptions' => ['class' => 'item'],
                        'layout' => "{items}",
                        //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
                        //'summary' => 'Показано {count} из {totalCount}',
                        'itemView' => function ($model, $key, $index, $widget) use($shareParent) {
                            //$model['share_name'] = FileSys::basename($model['share_path']);
                            if ($model['is_folder'] == UserFiles::TYPE_FOLDER) {
                                $link = Html::a(
                                    '<span class="glyphicon glyphicon-folder-open">&nbsp;</span>'. $model['file_name'],
                                    ["/folder/" . $model['share_group_hash'] . "/" . ($model['file_id'])]
                                );
                            } elseif ($model['is_folder'] == UserFiles::TYPE_TOP_FOLDER) {
                                $link = Html::a(
                                    '<b style="font-size: 20px; line-height: 14px;">' . $model['file_name'] . "</b>",
                                    ["/folder/" . $model['share_group_hash']]
                                );
                            } elseif ($model['is_folder'] == UserFiles::TYPE_UP_FOLDER) {
                                $link = Html::a(
                                    '<b style="font-size: 20px; line-height: 14px;">' . $model['file_name'] . "</b>",
                                    ["/folder/" . $model['share_group_hash'] . "/" . $shareParent->file_parent_id]
                                );
                            } else {
                                $link = Html::a(
                                    '<span class="glyphicon glyphicon-file">&nbsp;</span>' . $model['file_name'],
                                    ["/file/" . $model['share_hash']]
                                );
                            }


                            return '<tr data-key="0">
                                        <td width="80%">' . $link . '</td>
                                        <td>' . $model['file_size'] . '</td>
                                    </tr>';
                        },
                    ]);
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
