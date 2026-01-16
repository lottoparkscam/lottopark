<?php 
if (!defined('WPINC')) {
    die;
}
get_header(); ?>

<?php get_template_part('template-parts/header', 'main'); ?>
<?php get_template_part('template-parts/index', 'lotteries'); ?>
<?php get_template_part('template-parts/index', 'games'); ?>
<?php get_template_part('template-parts/index', 'messages'); ?>
<?php get_template_part('template-parts/index', 'mobile'); ?>
<?php get_template_part('template-parts/index', 'random_number_generator'); ?>
<?php get_template_part('template-parts/index', 'coverage'); ?>
<?php get_template_part('template-parts/index', 'brands'); ?>

<?php get_footer(); ?>
