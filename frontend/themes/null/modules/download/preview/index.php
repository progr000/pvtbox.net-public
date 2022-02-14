<?php
/* @var $this \yii\web\View */
/* @var \common\models\UserFiles $share */
/* @var array $servers */
?>
<style type="text/css">
    #share_opts{width: 100%; }
    .share_header{ width: 100%; }
    .share_main{clear: both; width: 100%; height: auto; overflow:hidden }
    .share_fileinfo{ float: left; width: 100%; height: auto; }
    .share_progress{ float: left; width: 100%; height: auto; }
    .share_state{ width: 100%; }
    .share_right {float: right;}
    #progress_ {width: 70%;  }
</style>

<div id="share_opts" hidden>
    <input id="share_hash" type="hidden" value="<?= $share->share_hash ?>">
    <input id="share_name" type="hidden" value="<?= $share->file_name ?>">
    <input id="share_size" type="hidden" value="<?= $share->file_size ?>">
    <input id="diff_file_uuid" type="hidden" value="<?= $share->diff_file_uuid ?>">
    <input id="stun_server_url" type="hidden" value="<?= $servers['stun'][0]['server_url'] ?>">
    <input id="sig_server_url"  type="hidden" value="<?= $servers['sign'][0]['server_url'] ?>">
    <select id="node_ids" hidden>
        <?php
        foreach ($nodes as $k => $v)
            echo "<option hidden value=\"{$v['node_id']}\"></option>\n";
        ?>
    </select>
</div>

<div class="share_header" id="header">
    <?php
    if (($share->share_group_hash) && ($share->file_parent_id)) {
        echo '<a href="/folder/' . $share->share_group_hash . '/' . $share->file_parent_id . '">Return to folder</a>';
    }
    ?>

    <br /><hr />
    <label>Get shared file</label>
    <a href="" class="share_right" id="a_refresh">Reload</a>
</div>

<div class="share_main" id="main">
    <div class="share_fileinfo">
        <table class="shared_file" id="shared_file">
            <tr><td>File name:</td><td id="out_file_name"></td></tr>
            <tr><td>File size:</td><td id="out_file_size"></td></tr>
            <tr><td>File hash:</td><td id="out_file_hash"></td></tr>
            <tr><td>Diff file UUID:</td><td id="out_diff_file_uuid"></td></tr>
            <tr><td>Node_ids:</td><td id="out_node_ids"></td></tr>
            <tr><td>Online nodes:</td><td id="out_online_nodes"></td></tr>
        </table>
    </div>
    <hr align="center" width="100%" size="3" color="#dddddd" />
    <div class="share_progress">
        <p>
            <button id="btn_getfile2" onclick="download_file();" disabled > Download file (v2) </button>
            <button id="btn_getfile3" onclick="download_file_by_app();" > Download file by app </button>
        </p>
        <p>
            progress indicator: <progress id="progress_" max="0" value="0"></progress>
        </p>
        <p>
            <label id="label_result"></label>
        </p>
    </div>
    <div>
        <a id="download"></a>
        <a id="download_by_app" hidden="true" ></a>
    </div>
</div>

<hr align="center" width="100%" size="3" color="#dddddd" />

<div class="share_state" id="state">
    <label id="label_state"></label>
</div>

<script src="/js/mod_download/check_browser.min.js"></script>
<script src="/js/mod_download/long.min.js"></script>
<script src="/js/mod_download/bytebuffer.min.js"></script>
<script src="/js/mod_download/protobuf.min.js"></script>
<script src="/js/mod_download/proto.min.js"></script>
<script src="/js/mod_download/signal_connection.min.js"></script>
<script src="/js/mod_download/signal_protocol.min.js"></script>
<script src="/js/mod_download/rtcdc2.min.js"></script>
<script src="/js/mod_download/transport.min.js"></script>
<script src="/js/mod_download/file_writer.min.js"></script>
<script src="/js/mod_download/download_task.min.js"></script>
<script src="/js/mod_download/main2.min.js"></script>
