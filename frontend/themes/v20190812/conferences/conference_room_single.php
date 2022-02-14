<?php
/** @var $this yii\web\View */

use common\models\UserConferences;

?>

<!-- list small participants video -->
<div class="participants-carousel-container delta-height-div view-single-mode">
    <div class="participants-carousel slick-initialized slick-slider">
        <button class="slick-prev slick-arrow slick-prev-participant" aria-label="Previous" type="button" style="">Previous</button>

            <div id="remote-video" class="hidden-overflow single-mode" data-view-mode="<?= UserConferences::VIEW_SINGLE ?>">
            </div>

        <button class="slick-next slick-arrow slick-next-participant" aria-label="Next" type="button" style="">Next</button>
    </div>
</div>

<!-- main stream video -->
<div class="main-video-container view-single-mode">
    <div id="main-video-stream" class="main-video-stream-div">
        <video id="main-video" playsinline=true></video>
    </div>
</div>
