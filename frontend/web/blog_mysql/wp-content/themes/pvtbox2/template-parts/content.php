<?php
/**
 * The template part for displaying post
 *
 * @package Sirat
 * @subpackage sirat
 * @since Sirat 1.0
 */
?>

<div id="post-<?php the_ID(); ?>" <?php post_class('inner-service'); ?>>
    <div class="post-main-box ">
        <div class="row m-0">

            <div class="new-text col-lg-12 col-md-12">
            <h3 class="section-title"><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title();?></a></h3>
            <div class="post-info">
                <span class="entry-date"><?php echo esc_html( get_the_date() ); ?></span><span>|</span>
                <span class="entry-author"> <?php the_author(); ?></span>
                <hr>
            </div>
            <p><?php
                if(has_post_thumbnail()) {
                    the_post_thumbnail();
                }
                $excerpt = get_the_excerpt();
                $custom = get_post_custom();
                //var_dump($custom);
                if (isset($custom['cont_preview_words'][0])) {
                    $cont_preview_words = intval($custom['cont_preview_words'][0]);
                } else {
                    $cont_preview_words = 100;
                }
                //https://hostenko.com/wpcafe/hacks/29-wordpress-tryukov-dlya-rabotyi-s-zapisyami-i-stranitsami/
                //https://codex.wordpress.org/Using_Custom_Fields#Usage
                //var_dump(get_post_meta($post->ID, 'cont_preview_words', true));
                echo esc_html( sirat_string_limit_words( $excerpt, $cont_preview_words ) );
                ?></p>
            <div class="more-btn">
                <a href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e( 'READ MORE', 'sirat' ); ?></a>
            </div>
        </div>
    </div>
</div>
</div>