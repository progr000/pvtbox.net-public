<?php
/**
 * The template part for header
 *
 * @package Sirat 
 * @subpackage sirat
 * @since Sirat 1.0
 */
?>

<?php if( get_theme_mod( 'sirat_contact_text') != '' || get_theme_mod( 'sirat_contact_call') != '' || get_theme_mod( 'sirat_header_search') != '' ) { ?>

<div class="top-bar">
	<div class="container">
		<div class="row">
			<div class="col-lg-3 col-md-3">
			    <?php if( get_theme_mod( 'sirat_contact_call') != '') { ?>
          			<p><i class="fas fa-phone"></i><?php echo esc_html(get_theme_mod('sirat_contact_call',''));?></p>
    			<?php } ?>
		    </div>
		    <div class="col-lg-4 col-md-4">
			    <?php if( get_theme_mod( 'sirat_contact_email') != '') { ?>
          			<p><i class="far fa-envelope"></i><?php echo esc_html(get_theme_mod('sirat_contact_email',''));?></p>
        		<?php }?>
		    </div>
		    <div class="<?php if(get_theme_mod('sirat_header_search')) { ?>col-lg-4 col-md-4" <?php } else { ?>col-lg-5 col-md-5 "<?php } ?> >
			    <?php dynamic_sidebar('social-links'); ?>
		    </div>
		    <?php if( get_theme_mod( 'sirat_header_search') != '') { ?>
	        	<div class="col-lg-1 col-md-1">
	          		<div class="search-box">
            			<span><i class="fas fa-search"></i></span>
	          		</div>
		        </div>
	      	<?php }?>
		</div>
		<div class="serach_outer">
	      	<div class="closepop"><i class="far fa-window-close"></i></div>
	      	<div class="serach_inner">
	        	<?php get_search_form(); ?>
	      	</div>
    	</div>
	</div>
</div>

<?php }?>