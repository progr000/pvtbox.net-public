<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\bootstrap\Modal;

?>

<?php
/*
Modal::begin([
    'options' => [
        'id' => 'install',
    ],
    'clientOptions' => [
        'show' => true,
        'data-show' => true,
        'keyboard' => false,
        'backdrop' => 'static',
    ],
    'closeButton' => false,
    'header' => null,
    'size' => 'modal-lg',
]);
*/
?>
<div id="install-header">
    <h1>Вы успешно зарегистрировались! Установите приложение {APP_NAME} для того чтобы начать пользоваться сервисом.</h1>
    <h3>Чем больше устройств подключено к вашему аккаунту {APP_NAME} тем быстрее работает ваше частное облако. Минимум необходимо 1 устройство.</h3>
    <br /><br /><br />
    <?= Html::a('<span id="button-text">Скачать бесплатно</span>', '/download', ['class'=>'btn-lg btn-primary']) ?>
    <br /><br /><br />
</div>
<?php
//Modal::end();
/*
$(document).ready(function() {
    //$('#install').modal({'show':true});
});
$('#install').on('hide.bs.modal', function() {
  return false;
})
*/
?>
