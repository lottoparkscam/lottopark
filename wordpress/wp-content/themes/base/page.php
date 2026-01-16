<?php 
if (!defined('WPINC')) {
    die;
}

get_header();

get_template_part('content', 'login-register-box-mobile');

// key is tag name in content, value is column name in database
$tags_to_replace_on_each_page = [
    'footer' => 'footer',
    'privacy' => 'privacy_policy',
    'terms' => 'terms_and_conditions',
    'country' => 'country'
];

$content = get_the_content();
$content = apply_filters( 'the_content', $content );
$content = str_replace( ']]>', ']]&gt;', $content );
$content = apply_filters('replace_wordpress_tags', $content, $tags_to_replace_on_each_page);

?>

<div class="content-area">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1><?php the_title(); ?></h1>
                    <?php
                    $isTermsPage = Lotto_Platform::is_page('terms');
                    $isPrivacyPage = Lotto_Platform::is_page('privacy');

                    if ($isTermsPage || $isPrivacyPage):
                    ?>
                        <a class="btn btn-primary btn-print" href="#" onclick="window.print(); return false;"><i class="fas fa-print"></i></a>
                    <?php endif;?>

                    <?= $content ?>
                </article>
            </section>
        </div>
    </div>
</div>
<?php 
get_footer();
