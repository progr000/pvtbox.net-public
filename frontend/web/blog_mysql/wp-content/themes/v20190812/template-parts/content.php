<?php
/**
 * The template part for displaying post
 *
 * @package Sirat
 * @subpackage sirat
 * @since Sirat 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('inner-service blog-item'); ?>>
    <header class="blog-item__header">
        <div class="blog-item__title"><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title();?></a></div>
        <div class="blog-item__meta">
            <time class="blog-item__publishedon"><?= esc_html( get_the_date() ); ?></time><span class="blog-item__author"><?php the_author(); ?></span>
        </div>
    </header>
    <div class="blog-item__intro">
        <?php
            if(has_post_thumbnail()) {
                the_post_thumbnail();
            }
        ?>
        <p>
            <?php
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
            ?>
        </p>
        <a class="more-link btn primary-btn sm-btn" href="<?php echo esc_url(get_permalink()); ?>"><?= Yii::t('app/blog', 'Read_more') ?></a>
    </div>
</article>
