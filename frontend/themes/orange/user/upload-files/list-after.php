<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <!--
    <tr class="template-download fade">
        <td nowrap="nowrap" style="width: 100%;">
            {% if (file.thumbnailUrl) { %}
                <span class="preview">
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}" alt="thumb" /></a>
                </span>
            {% } %}
            <p class="name">
                <div style="max-width: 120px; overflow-x: hidden;">{%=file.name%}</div>
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger"><?= Yii::t('user/filemanager', 'Error_single') ?></span> {%=file.error%}</div>
            {% } %}
        </td>
        <td nowrap="nowrap" style="width: 100px;">
            <p class="size">{%=o.formatFileSize(file.size)%}</p>
        </td>
        <td nowrap="nowrap" style="width: 100px;">
        <p class="">
            <button class="-btn -btn-warning cancel btn-cancelUpload" style="min-width: 80px !important;">
                <i class="glyphicon glyphicon-ban-circle"></i>
                <span><?= Yii::t('user/filemanager', 'Cancel_single') ?></span>
            </button>
            </p>
        </td>
    </tr>
    -->
{% } %}

</script>
