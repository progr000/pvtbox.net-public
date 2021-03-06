<?php
/**
 * The template part for top header
 *
 * @package Sirat 
 * @subpackage sirat
 * @since Sirat 1.0
 */
?>

<div class="middle-header">
  <div class="container">
    <div class="row">
      <div class="col-lg-3 col-md-3">
        <div class="logo">
          <?php if( has_custom_logo() ){ sirat_the_custom_logo();
            }else{ ?>
              <h1><a href="/"><img src="/themes/orange/images/logo.svg" alt="Pvtbox"></a></h1>
              <?php $description = get_bloginfo( 'description', 'display' );
              if ( $description || is_customize_preview() ) : ?>
              <p class="site-description" style="padding: 7px 0 0 40px;"><?php echo esc_html($description); ?></p>
          <?php endif; } ?>
        </div>
      </div>
      <div class="col-lg-9 col-md-9">
        <?php get_template_part( 'template-parts/header/navigation' ); ?>
      </div>
    </div>
  </div>
</div>