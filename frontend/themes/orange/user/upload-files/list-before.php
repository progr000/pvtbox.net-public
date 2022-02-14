<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td _nowrap="nowrap" style="width: 100%;">
            <p class="name">
                <!--<span class="preview"></span><br/>-->
                <div style="max-width: 120px; overflow-x: hidden;">{%=file.name%}</div>
            </p>
            <strong class="error text-danger"></strong>
        </td>
        <td nowrap="nowrap" style="width: 100px;">
            <p class="size"><?= Yii::t('user/filemanager', 'Processing_single') ?></p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td nowrap="nowrap" style="width: 100px;">
            <p class="">
                {% if (!i && !o.options.autoUpload) { %}
                    <button class="-btn -btn-primary start btn-startUpload" disabled style="min-width: 80px !important; margin-bottom: 3px;">
                        <i class="glyphicon -glyphicon-upload"></i>
                        <span><?= Yii::t('user/filemanager', 'Start_single') ?></span>
                    </button>
                    <br />
                {% } %}
                {% if (!i) { %}
                    <button class="-btn -btn-warning cancel btn-cancelUpload" style="min-width: 80px !important;">
                        <i class="glyphicon -glyphicon-ban-circle"></i>
                        <span><?= Yii::t('user/filemanager', 'Cancel_single') ?></span>
                    </button>
                {% } %}
            </p>
        </td>
    </tr>
{% } %}
</script>