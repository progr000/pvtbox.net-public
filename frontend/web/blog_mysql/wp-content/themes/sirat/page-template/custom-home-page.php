<?php
/**
 * Template Name: Custom Home Page
 */

get_header(); ?>

<?php do_action( 'sirat_before_slider' ); ?>

<?php if( get_theme_mod( 'sirat_slider_arrows') != '') { ?>
<section id="slider">
  <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel"> 
    <?php $sirat_pages = array();
      for ( $count = 1; $count <= 3; $count++ ) {
        $mod = intval( get_theme_mod( 'sirat_slider_page' . $count ));
        if ( 'page-none-selected' != $mod ) {
          $sirat_pages[] = $mod;
        }
      }
      if( !empty($sirat_pages) ) :
        $args = array(
          'post_type' => 'page',
          'post__in' => $sirat_pages,
          'orderby' => 'post__in'
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) :
          $i = 1;
    ?>     
    <div class="carousel-inner" role="listbox">
      <?php while ( $query->have_posts() ) : $query->the_post(); ?>
        <div <?php if($i == 1){echo 'class="carousel-item active"';} else{ echo 'class="carousel-item"';}?>>
          <img src="<?php the_post_thumbnail_url('full'); ?>"/>
          <div class="carousel-caption">
            <div class="inner_carousel">
              <h2><?php the_title(); ?></h2>
              <p><?php $excerpt = get_the_excerpt(); echo esc_html( sirat_string_limit_words( $excerpt,25 ) ); ?></p>
              <div class="more-btn">
                <a href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e( 'READ MORE', 'sirat' ); ?></a>
              </div>
            </div>
          </div>
        </div>
      <?php $i++; endwhile; 
      wp_reset_postdata();?>
    </div>
    <?php else : ?>
        <div class="no-postfound"></div>
    <?php endif;
    endif;?>
    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
    </a>
  </div>
  <div class="clearfix"></div>
</section>
<?php } ?>

<?php do_action( 'sirat_after_slider' ); ?>

<section id="serv-section">
  <div class="container">
    <div class="heading-box">
      <div class="row">
        <?php if( get_theme_mod( 'sirat_section_title') != '') { ?>
          <div class="col-lg-4 col-md-4">        
            <h3><?php echo esc_html(get_theme_mod('sirat_section_title',''));?></h3>        
          </div>
        <?php } ?>
        <?php if( get_theme_mod( 'sirat_section_text') != '') { ?>
          <div class="col-lg-8 col-md-8">
            <p><?php echo esc_html(get_theme_mod('sirat_section_text',''));?></p>        
          </div>
        <?php } ?>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-8 col-md-12">
        <div class="row">
          <?php
            $catData =  get_theme_mod('sirat_our_services','');
            if($catData){
            $page_query = new WP_Query(array( 'category_name' => esc_html($catData,'sirat'))); ?>
            <?php while( $page_query->have_posts() ) : $page_query->the_post(); ?>
            <div class="col-lg-4 col-md-4">
              <div class="serv-box">
                <?php the_post_thumbnail(); ?>
                <h4><?php the_title(); ?></h4>
                <p><?php $excerpt = get_the_excerpt(); echo esc_html( sirat_string_limit_words( $excerpt,12 ) ); ?></p>
                <a href="<?php the_permalink(); ?>"><i class="fas fa-arrow-right"></i></a>
              </div>
            </div>
            <?php endwhile;
            wp_reset_postdata();
          } ?>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <?php $sirat_pages = array();
          for ( $count = 0; $count <= 0; $count++ ) {
            $mod = absint( get_theme_mod( 'sirat_about_page' ));
            if ( 'page-none-selected' != $mod ) {
              $sirat_pages[] = $mod;
            }
          }
          if( !empty($sirat_pages) ) :
            $args = array(
              'post_type' => 'page',
              'post__in' => $sirat_pages,
              'orderby' => 'post__in'
            );
            $query = new WP_Query( $args );
            if ( $query->have_posts() ) :
              $count = 0;
              while ( $query->have_posts() ) : $query->the_post(); ?>
                <div class="box">
                  <?php the_post_thumbnail(); ?>
                  <div class="box-content">
                    <div class="inner-content">
                      <h3 class="title"><?php the_title(); ?></h3>
                      <p class="post"><?php $excerpt = get_the_excerpt(); echo esc_html( sirat_string_limit_words( $excerpt,10 ) ); ?></p>
                      <div class="about-btn">
                        <a href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e( 'READ MORE', 'sirat' ); ?></a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php $count++; endwhile; ?>
            <?php else : ?>
              <div class="no-postfound"></div>
            <?php endif;
          endif;
          wp_reset_postdata();
        ?>
      </div>
    </div>
  </div>
</section>

<?php do_action( 'sirat_after_second_section' ); ?>

<div id="content-vw">
  <div class="container">
    <?php while ( have_posts() ) : the_post(); ?>
      <?php the_content(); ?>
    <?php endwhile; // end of the loop. ?>
  </div>
</div>

<?php get_footer(); ?>
