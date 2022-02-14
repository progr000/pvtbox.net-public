<?php
/**
 * The template part for displaying single post content
 *
 * @package Sirat
 * @subpackage sirat
 * @since Sirat 1.0
 */
?>


<article id="post-<?php the_ID(); ?>" <?php post_class('inner-service post'); ?>>
    <header class="post__header">
        <div class="post__meta">
            <time class="post__publishedon"><?php the_date(); ?></time><span class="post__author"><?php the_author(); ?></span><span class="post__comments"><?php comments_number( __('0 Comment', 'sirat'), __('0 Comments', 'sirat'), __('% Comments', 'sirat') ); ?></span>
        </div>
        <h1><?php the_title(); ?></h1>
    </header>
    <?php if (has_post_thumbnail()) { ?>
    <figure class="post__media" style="text-align: center;"><img src="<?php the_post_thumbnail_url('full'); ?>" alt=""></figure>
    <?php } ?>
    <?php the_content(); ?>
    <div class="tags"><?php the_tags(); ?></div>
</article>
<?php
// If comments are open or we have at least one comment, load up the comment template
/*
if (comments_open() || '0' != get_comments_number()) {
    comments_template();
}
*/
?>
<div class="blog-nav">
    <?php

    $nav = get_the_post_navigation_([
        '%title',
        '%title',
    ]);

    ?>

    <div class="blog-nav__item blog-nav__item--prev">
        <?php if ($nav['previous']) { ?>
        <div class="blog-nav__dir">Previous</div><?= $nav['previous'] ?>
        <?php } ?>
    </div>
    <div class="blog-nav__item blog-nav__item--next">
        <?php if ($nav['next']) { ?>
        <div class="blog-nav__dir">Next</div><?= $nav['next'] ?>
        <?php } ?>
    </div>
</div>
