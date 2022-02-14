<?php
/**
 * The template for displaying search forms in Sirat
 *
 * @package Sirat
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="search" class="search-field" aria-label="Search" placeholder="<?php echo esc_attr_x( 'Search', 'placeholder','sirat' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<input type="submit" class="search-submit btn primary-btn wide-btn" value="<?php echo esc_attr_x( 'Search', 'submit button','sirat' ); ?>" />
</form>