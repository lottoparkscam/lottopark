<?php
if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');
?>

<div class="main-width">
    <section class="page-content">
        <article class="page">
            <h1 class="header-payment"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </article>
        <?php
            if (function_exists("lotto_platform_payment_box")):
                echo lotto_platform_payment_box();
            endif;
        ?>
    </section>
</div>
<?php
get_footer();
