<?php

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\editable\Editable;


$this->title = 'Мои устройства';
//$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile('/js/jstree/jstree.min.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerJsFile('/js/jstree/init.jstree.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile('/css/jstree/style.min.css');
$this->registerCssFile('/css/jstree/jstree.css');
?>
<div class="site-devices">
    <h1><?= Html::encode($this->title) ?></h1>


    <div class="col-lg-12">
        <div id="datatree">
            <div id="show_select_device">Выберите устройство для просмотра его ФС</div>
            <div id="show_device_fs" style="display: none;">Просмотр ФС устройства:</div>
        </div>
        <!--
        <div id="data">
            <div class="content code" style="display:none;"><textarea id="code" readonly="readonly"></textarea></div>
            <div class="content folder" style="display:none;"></div>
            <div class="content image" style="display:none; position:relative;"><img src="" alt="" style="display:block; position:absolute; left:50%; top:50%; padding:0; max-height:90%; max-width:90%;" /></div>
            <div class="content default" style="text-align:center;">Select a file from the tree.</div>
        </div>
        -->
    </div>

</div>
