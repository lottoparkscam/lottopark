<?php

use Presenters\Wordpress\Base\Views\PageActivatedPresenter;

if (!defined('WPINC')) {
    die;
}
$whitelabel = Lotto_Settings::getInstance()->get('whitelabel');
$isUser = Lotto_Settings::getInstance()->get('is_user');
?>
<?php get_header();
?>
<?php
    if ($isUser && (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_OPTIONAL) :
        /** @var PageActivatedPresenter $presenter */
        $presenter = Container::get(PageActivatedPresenter::class);
        echo $presenter->view();
    else :
?>
<div class="content-area">
<div class="main-width content-width">
	<div class="content-box">
	<section class="page-content">
		<article class="page">

			<h1 class="text-center"><?php the_title(); ?></h1>
			<?php
                if ($whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_OPTIONAL):
                    echo '<p style="text-align: center;">' .
                        _("Thank you for choosing us! You have been logged in.") .
                        "</p>";
                    echo '<p style="text-align: center;">' .
                        _("To fully activate your account and get access to all functionalities, please confirm your e-mail address by following the confirmation link we have sent you to your e-mail.") .
                        "</p>";
                elseif ($whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_REQUIRED):
                    echo '<p style="text-align: center;">' .
                        _("To activate your account please confirm your e-mail address by following the confirmation link we have sent you to your e-mail.") .
                        "</p>";
                endif;

                the_content();
            ?>
		</article>
        <?php Lotto_Helper::hook("page-activation"); ?>
	</section>
	</div>
</div>

<?php
    if ($whitelabel['user_activation_type'] != Helpers_General::ACTIVATION_TYPE_REQUIRED):
        the_widget(
            'Lotto_Widget_List',
            array('count' => 3, 'countdown' => 2, 'display' => 2, 'type' => 2)
        );
    endif;
?>
</div>
<?php
    endif;
    get_footer();
?>