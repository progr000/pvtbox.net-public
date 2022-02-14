<?php
/** @var \dosamigos\fileupload\FileUploadUI $this */
use yii\helpers\Html;

$context = $this->context;
?>
<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
<div class="row fileupload-buttonbar">
    <div class="fileUploaderTable" _class="col-lg-5" style="width: 340px; text-align: center;">

        <!-- The fileinput-button span is used to style the file input field as button -->
        <span class="btn -btn-success fileinput-button btn-addFiles">
            <i class="glyphicon -glyphicon-plus"></i>
            <span><?= Yii::t('user/filemanager', 'Add_files') ?></span>
            <?php
                $name = $context->model instanceof \yii\base\Model && $context->attribute !== null ? Html::getInputName($context->model, $context->attribute) : $context->name;
                $value = $context->model instanceof \yii\base\Model && $context->attribute !== null ? Html::getAttributeValue($context->model, $context->attribute) : $context->value;
                echo Html::hiddenInput($name, $value).Html::fileInput($name, $value, $context->options);
            ?>
        </span>
        <!--
        <button type="submit" class="-btn -btn-primary start btn-startUpload">
            <i class="glyphicon -glyphicon-upload"></i>
            <span><?= Yii::t('user/filemanager', 'Start_upload') ?></span>
        </button>
        -->
        <button type="reset" class="-btn -btn-warning cancel btn-cancelUpload" id="cancelAllUploads">
            <i class="glyphicon -glyphicon-ban-circle"></i>
            <span><?= Yii::t('user/filemanager', 'Cancel_upload') ?></span>
        </button>
        <br />
        <!--
        <button type="button" class="btn btn-danger delete">
            <i class="glyphicon -glyphicon-trash"></i>
            <span><?= Yii::t('user/filemanager', 'Delete') ?></span>
        </button>
        <input type="checkbox" class="toggle">
        <!-- The global file processing state -- >

        <span class="fileupload-process"></span>
        -->
        <?= Yii::t('user/filemanager', 'You_can_drag_and_drop') ?>

        <div class="fileupload-progress fade" style="margin-bottom: 0px;">
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="margin-bottom: 0px;">
                <div class="progress-bar progress-bar-success" style="width:0%;"></div>
            </div>
            <!-- The extended global progress state -->
            <div class="progress-extended-2" style="margin-bottom: 5px; font-size: 11px;">&nbsp;</div>
        </div>

        <div class="scrollbar-program">
            <div class="table__body-cont" id="fileversions-list--">
                <!-- The table listing the files available for upload/download -->
                <table role="presentation" class="table table-striped" style="width: 95%;">
                    <tbody class="files" id="table-list-uploaded-files"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
