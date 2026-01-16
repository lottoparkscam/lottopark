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
                    <h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </article>
                <?php
                    $args = array(
                        'taxonomy' => 'faq-category',
                        'hide_empty' => false,
                        'title_li' => ''
                    );
                    $cat_arr = get_categories($args);
                ?>
                <section class="faq">
                    <nav class="faq-nav">
                        <div class="mobile-only">
                            <label for="faq-mobile-cats" class="faq-cats-label"><?php echo Security::htmlentities(_("Category")); ?>:</label> 
                            <select id="faq-mobile-cats" class="faq-cats">
                                <option value="0"><?php echo Security::htmlentities(_("choose")); ?></option>
                                <?php
                                    $i = 0;
                                    foreach ($cat_arr as $cat_item):
                                        $i++;
                                ?>
                                        <option value="<?php echo htmlspecialchars($cat_item->term_id); ?>" 
                                                data-link="<?php echo htmlspecialchars(get_term_link($cat_item->term_id)); ?>"><?php echo $i; ?>. <?php echo $cat_item->name; ?></option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                        <ol>
                            <?php foreach ($cat_arr as $cat_item): ?>
                                <li><a href="<?php echo htmlspecialchars(get_term_link($cat_item->term_id)); ?>"><?php echo $cat_item->name; ?></a></li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </section>
                <div class="clearfix"></div>
            </section>
        </div>
    </div>
</div>
<?php get_footer(); ?>
