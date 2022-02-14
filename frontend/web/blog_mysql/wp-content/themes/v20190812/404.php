<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Sirat
 */

get_header(); ?>

<div id="content-vw">
	<div class="container">
    	<h1><?php printf( '<strong>%s</strong> %s', esc_html__( '404','sirat' ), esc_html__( 'Not Found', 'sirat' ) ) ?></h1>
		<p class="text-404"><?php esc_html_e( 'Looks like you have taken a wrong turn&hellip', 'sirat' ); ?></p>
		<p class="text-404"><?php esc_html_e( 'Dont worry&hellip it happens to the best of us.', 'sirat' ); ?></p>
		<div class="more-btn">
	        <a href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e( 'READ MORE', 'sirat' ); ?></a>
	    </div>
		<div class="clearfix"></div>
	</div>
</div>

<?php get_footer(); ?>