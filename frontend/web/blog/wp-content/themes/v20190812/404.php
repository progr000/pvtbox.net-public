<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Sirat
 */

get_header(); ?>

	<div class="content container">
		<h1><?= Yii::t('app/blog', 'Blog') ?></h1>
		<div class="content-wrap">

			<div class="main-content">
				<div class="blog">

					<div class="content container">
						<h1 class="centered">Not Found (#404)</h1>
						<div class="page-section-description">
							Page not found.
						</div>
					</div>

				</div>
			</div>


			<div class="sidebar">

				<?= getBackToMainBlock() ?>

				<?php dynamic_sidebar('sidebar-1'); ?>

			</div>


		</div>
	</div>
<!--
	<div class="content container">
		<h1 class="centered">Not Found (#404)</h1>
		<div class="page-section-description">
			Page not found.
		</div>
	</div>
-->
<?php get_footer(); ?>