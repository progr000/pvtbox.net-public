<?php
/**
 * The template for displaying home page.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Sirat
 */

get_header(); ?>

    <div class="content container">
        <h1><?= Yii::t('app/blog', 'Blog') ?></h1>
        <div class="content-wrap">

            <div class="main-content">
                <div class="blog">

                    <?php
                    if ( have_posts() ) {
                        /* Start the Loop */
                        while (have_posts()) {
                            the_post();
                            get_template_part('template-parts/content', get_post_format());
                        }
                    } else {
                        get_template_part('no-results');
                    }
                    ?>

                    <div class="navigation">
                        <?php
                        // Previous/next page navigation.
                        the_posts_pagination( array(
                            'prev_text'          => __( '«', 'sirat' ),
                            'next_text'          => __( '»', 'sirat' ),
                            'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( '', 'sirat' ) . ' </span>',
                        ) );
                        ?>
                    </div>


                </div>
            </div>


            <div class="sidebar">

                <?= getBackToMainBlock() ?>

                <?php dynamic_sidebar('sidebar-1'); ?>

            </div>


        </div>
    </div>

<?php get_footer(); ?>