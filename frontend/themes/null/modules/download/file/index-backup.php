<?php

/* @var $this \yii\web\View */

use frontend\assets\AppAsset;

AppAsset::register($this);
//$this->registerCssFile('/js/webrtc/roboto.css');
//$this->registerCssFile('/js/webrtc/main.css');
$this->registerCssFile('/js/webrtc/main2.css');
$this->registerJsFile('/js/webrtc/adapter.js');
$this->registerJsFile('/js/webrtc/common.js');
$this->registerJsFile('/js/webrtc/main.js');
?>
<div id="container">

    <h1><a href="//webrtc.github.io/samples/" title="WebRTC samples homepage">WebRTC samples</a> <span>Transmit text</span></h1>

    <div id="buttons">
        <button id="startButton">Start</button>
        <button id="sendButton" disabled>Send</button>
        <button id="closeButton" disabled>Stop</button>
    </div>

    <div id="sendReceive">
        <div id="send">
            <h2>Send</h2>
            <textarea id="dataChannelSend" disabled placeholder="Press Start, enter some text, then press Send."></textarea>
        </div>
        <div id="receive">
            <h2>Receive</h2>
            <textarea id="dataChannelReceive" disabled></textarea>
        </div>
    </div>

    <p>View the console to see logging.</p>

    <p>The <code>RTCPeerConnection</code> objects <code>localConnection</code> and <code>remoteConnection</code> are in global scope, so you can inspect them in the console as well.</p>

    <p>For more information about RTCDataChannel, see <a href="http://www.html5rocks.com/en/tutorials/webrtc/basics/#toc-rtcdatachannel" title="RTCDataChannel section of HTML5 Rocks article about WebRTC">Getting Started With WebRTC</a>.</p>

    <a href="https://github.com/webrtc/samples/tree/gh-pages/src/content/datachannel/basic" title="View source for this page on GitHub" id="viewSource">View source on GitHub</a>
</div>
