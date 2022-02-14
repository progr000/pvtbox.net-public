<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content-vw">
 *
 * @package Pvtbox
 */
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../../../../../vendor/autoload.php');
require(__DIR__ . '/../../../../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../../../../../common/config/bootstrap.php');
require(__DIR__ . '/../../../../../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../../../../../common/config/main.php'),
	require(__DIR__ . '/../../../../../../common/config/main-local.php'),
	require(__DIR__ . '/../../../../../config/main.php'),
	require(__DIR__ . '/../../../../../config/main-local.php')
);

unset($config['components']['urlManager']);
$application = new yii\web\Application($config);
defined( 'THEME_PATH' ) or define( 'THEME_PATH', __DIR__ . "/../../../../../themes/" . DESIGN_THEME );
//echo $application->view->renderPhpFile(__DIR__ . "/../../../../../themes/" . DESIGN_THEME . "/layouts/cookie_and_badge.php");
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="Description" content="<?php bloginfo( 'blogdescription' ) ?>Pvtbox Sync blog">
	<meta name="keywords" content="Sync app between devices, Sync app content between iphone and ipad, sync app android, peer to peer file transfer software, peer to peer file transfer app, peer to peer file transfer over internet, file transfer app, file transfer automation, file transfer from android to mac, file transfer between windows and linux, file transfer laptop to laptop" />
	<meta name="viewport" content="width=device-width">
	<?php wp_head(); ?>
	<link href="/themes/orange/css/style_00.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_01.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_02.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_03.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_04.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_05.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_06.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_07.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_08.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_09.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/style_10.css?v=<?php uniqid() ?>" rel="stylesheet">
	<link href="/themes/orange/css/guest.css?v=<?php uniqid() ?>" rel="stylesheet">
	<style>
		html {
			margin-top: 0px !important;
		}
		html {
			margin-top: 0px !important;
		}
		.footer-menu a:hover {
			text-decoration: underline !important;
			-webkit-transition-duration: 0s;
			-moz-transition-duration: 0s;
			-o-transition-duration: 0s;
			transition-duration: 0s;
		}
		.footer-menu a.nolink,
		.footer-menu a.nolink:hover {
			color: #FFFFFF;
			text-decoration: none !important;
			cursor: default;
		}
	</style>
	<?php
	if (file_exists(THEME_PATH . "/layouts/favicon.php")) {
		require_once(THEME_PATH . "/layouts/favicon.php");
	}
	?>
</head>
<body>

<div class="total-container total-container--indent" id="total-container-id">

	<!-- .header -->
	<!-- .header -->
	<div class="header header--fix">

		<div class="header-strip">

			<div class="header-strip__cont">

				<div class="header-strip__row">

					<div class="header-strip__col">
						<span class="header-strip-text">Fast and secure file transfer & sync directly across your devices</span>
					</div>

					<div class="header-strip__col">
						<a class="header-strip-link" href="/support">Support</a>
						<a class="header-strip-link-small" href="/support">@</a>
					</div>

				</div>

			</div>

		</div>


		<div class="header-inform">

			<div class="header-inform__cont">

				<div class="header-inform__row">

					<div class="header-inform__col">

						<div class="logo">
							<a href="/"><img src="/themes/orange/images/logo_new.svg" alt="Pvtbox" /></a>
							<span class="btn-default login-dialog" id="btn-login-dialog-mobile" data-toggle="modal" data-target="#entrance" data-whatever="log">Sign in</span>
						</div>

					</div>

					<div class="header-inform__col">

						<div class="menu">
							<ul>
								<li><a href="/download">Download</a></li>
								<li><a href="/features">Features</a></li>
								<li><a href="/pricing">Pricing</a></li>
								<li class="active"><a href="/blog">Blog</a></li>
								<li><a --data-toggle="modal" --data-target="#entrance" --data-whatever="reg" class="signup-dialog" href="/?signup=signup">Registration</a></li>
							</ul>
						</div>

						<a class="btn-default login-dialog" id="btn-login-dialog" --data-toggle="modal" --data-target="#entrance" --data-whatever="log" href="/?login=login">Sign in</a>

					</div>

				</div>

			</div>

		</div>

	</div>
	<!-- END .header -->
	<!-- END .header -->