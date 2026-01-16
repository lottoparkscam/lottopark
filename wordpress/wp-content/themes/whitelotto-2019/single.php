<?php get_header(); ?>
<?php get_template_part('template-parts/header', 'small'); ?>

    <article class="container">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
        <?php endif; ?>
    </article>
<?php get_footer(); ?>