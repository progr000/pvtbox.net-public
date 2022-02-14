<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Sirat
 */

use yii\helpers\Url;

get_header();
?>
<div class="content container">
    <!--<h1><?= Yii::t('app/blog', 'Blog') ?></h1>-->
    <div class="content-wrap">

        <div class="main-content">
            <div class="blog">

                <?php
                if (have_posts()) {
                    /* Start the Loop */
                    while (have_posts()) {
                        the_post();
                        get_template_part('template-parts/single-post-layout');
                    }
                } else {
                    get_template_part('no-results');
                }
                ?>

                <div class="navigation" style="text-align: center;">
                    <?php
                    // Previous/next page navigation.
                    the_posts_pagination( array(
                        'prev_text'          => __( '«', 'sirat' ),
                        'next_text'          => __( '»', 'sirat' ),
                        'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( '', 'sirat' ) . ' </span>',
                    ) );
                    ?>
                    <a href="<?= Url::to('/blog', CREATE_ABSOLUTE_URL) ?>">Back to main</a>
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