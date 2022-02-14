<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Pvtbox
 */
?>

<?php
$user = Yii::$app->user->identity;
/* begin footer of site */
//var_dump($user);exit;
Yii::$app->user->isGuest
    ? render('layouts/footer_guest', ['user' => $user, 'static_action' => $static_action])
    : render('layouts/footer_logged', ['user' => $user, 'static_action' => $static_action]);
/* end footer of site */
?>

</div>
<!-- end .page #total-container-id-->

<!-- begin .popups-->
<?php
render('layouts/cookie_and_badge');
?>
<!-- end .popups-->


<!-- begin service (javascript and to-top-button) -->
<?php render('layouts/service'); ?>
<?php
foreach (Yii::$app->params['jsAssets'] as $j) {
    echo '<script src="' . scriptsNoCache($j) . '"></script>' . "\n";
}
if (Yii::$app->user->isGuest) {
    $str_js = "var IS_GUEST = true;\n";
} else {
    $str_js = "var IS_GUEST = false;\n";
}
?>
<script>
    <?= $str_js ?>
</script>
<!-- end service (javascript and to-top-button) -->

</body>
</html>