<?php
if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');
?>
<div class="content-area">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <?php
                        $oldpost = $post;
                        $post = get_post(
                            apply_filters(
                                'wpml_object_id',
                                lotto_platform_get_post_id_by_slug('faq'),
                                'page',
                                true
                            )
                        );
                        setup_postdata($post);
                    ?>
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </article>
                <?php
                    wp_reset_postdata();
                    $post = $oldpost;
                    $args = array(
                        'taxonomy' => 'faq-category',
                        'hide_empty' => false,
                        'title_li' => ''
                    );

                    $cat = get_queried_object();
                    $cat_arr = get_categories($args);
                ?>
                <section class="faq">
                    <nav class="faq-nav">
                        <div class="mobile-only">
                            <label for="faq-mobile-cats" class="faq-cats-label"><?php echo Security::htmlentities(_("Category")); ?>:</label> 
                            <select id="faq-mobile-cats" class="faq-cats">
                                <option data-link="<?php echo lotto_platform_get_permalink_by_slug('faq'); ?>" 
                                        value="0"><?php echo Security::htmlentities(_("choose category")); ?></option>
                                <?php
                                    foreach ($cat_arr as $cat_item):
                                ?>
                                        <option<?php if ($cat->term_id == $cat_item->term_id): echo ' selected="selected"'; endif; ?> 
                                            value="<?php echo htmlspecialchars($cat_item->term_id); ?>" 
                                            data-link="<?php echo htmlspecialchars(get_term_link($cat_item->term_id)); ?>"><?php echo $cat_item->name; ?></option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        <ol>
                            <?php
                                foreach ($cat_arr as $cat_item):
                            ?>
                                    <li<?php if ($cat->term_id == $cat_item->term_id): echo ' class="current-cat"'; endif; ?>><a href="<?php echo htmlspecialchars(get_term_link($cat_item->term_id)); ?>"><?php echo $cat_item->name; ?><?php if ($cat->term_id == $cat_item->term_id): echo '<span class="faq-item-active-mark"></span>'; endif; ?></a></li>
                            <?php
                                endforeach;
                            ?>
                        </ol>
                    </nav>

                    <article class="faq-content">
                        <h2><?php echo Security::htmlentities($cat->name); ?></h2>
                        <?php
                            if (have_posts()):
                        ?>
                                <div class="faq-content-answers">
                                    <?php
                                        while (have_posts()):
                                            the_post();
                                    ?>
                                            <h3><a href="#" class="faq-toggle"><span class="faq-toggle-plus"><span class="fa fa-plus" aria-hidden="true"></span></span><span class="faq-title"><?php the_title(); ?></span></a></h3>
                                            <div class="faq-answer hidden-normal">
                                            <?php the_content(); ?>
                                            </div>
                                    <?php
                                        endwhile;
                                    ?>
                                </div>
                        <?php
                            else:
                                echo Security::htmlentities(_("This FAQ category is empty!"));
                            endif;
                        ?>
                    </article>
                    <div class="clearfix"></div>
                </section>
            </section>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<?php
get_footer();
