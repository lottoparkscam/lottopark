<?php /* Template page */ ?>
<?php
if(!defined('WPINC'))
{
    die;
}
?>
<?php get_header(); ?>
<?php get_template_part('template-parts/header', 'small'); ?>

    <article class="container">
        <?= the_content() ?>
        <div class="text-center">
            <a href="/solution" class="btn">Learn more</a>
            <a href="/contact" class="btn ml-3">Contact us</a>
        </div>
    </article>


<?php get_footer(); ?>