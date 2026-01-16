<?php /* Template name: SEO1 */ ?>
<?php
if (!defined('WPINC')) {
    die;
}
?>
<?php get_header(); ?>
<?php get_template_part('template-parts/header', 'small'); ?>

    <section class="container section-center text-center">
        <p class="text-left">Everybody heard about lotteries and their great winners. Jackpots worth hundreds of
            millions heat up the imagination of players who buy tickets, dreaming about claiming the grand prize. Almost
            each day, lottery business creates a new millionaire. There’s also the other side - the business itself.
            Only during the last year, the industry has generated over $279 billion in ticket sales. The profits made by
            lotteries are even harder to imagine than the feeling of hitting the jackpot yourself. Is it still possible
            to become a part of this great business? Thanks to WhiteLotto, it’s easier than you may think. What you need
            is our lottery turnkey solution</p>
    </section>
<?= get_template_part('template-parts/seo', 'circle1'); ?>
    <section class="container section-center text-center">
        <p class="text-left">WhiteLotto has developed software suited both for new and existing websites and businesses,
            allowing them to sell tickets of the world’s biggest lotteries right away. You can even jump-start your
            business without having a website yet. Our software will be configured by our top-notch in-house developers,
            designed and customized by our market experienced designers to boost your income. Exactly like a true
            lottery turnkey solution should like.</p>
    </section>
<?= get_template_part('template-parts/seo', 'circle2'); ?>
    <section class="container section-center">
        <p><?= the_content() ?></p>
        <br>
        <div class="text-center">
            <a href="https://whitelotto.com/solution" class="btn">Learn more</a>
            <a href="https://whitelotto.com/contact" class="btn ml-3">Contact us</a>
        </div>
    </section>
<?php get_footer(); ?>