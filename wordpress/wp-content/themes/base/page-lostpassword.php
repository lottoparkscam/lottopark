<?php 
if (!defined('WPINC')) {
    die;
}

get_header(); 
?>
<div class="content-area">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </article>
                <?php 
                    if (function_exists("lotto_platform_lostpassword_box")):
                        echo lotto_platform_lostpassword_box();
                    endif;
                ?>
            </section>
        </div>
    </div>
</div>
<?php 

get_footer(); 
