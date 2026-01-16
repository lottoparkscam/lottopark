<?php

use Helpers\UrlHelper;

?>

<?php if (!empty($visibility) && $visibility === 'show'):?>
    <div class="main-width">
        
        <div class="widget-container">
            <div class="row widget-header">
                <div class="widget-title"><?= _("How to play?") ?></div>
            </div>
            
            <div class="row widget-content">
                <div class="col-left">

                    <div class="row widget-pills" role="tablist">
                        <button class="btn active" data-target="1" role="tab" aria-selected="true">
                            <span class="btn-icon"><i class="fa fa-fw fa-mouse-pointer" aria-hidden="true"></i></span>
                            <span class="btn-text"><?= _("Register") ?></span>
                        </button>
                        <button class="btn" data-target="2" role="tab" aria-selected="false">
                            <span class="btn-icon"><i class="fa fa-fw fa-list" aria-hidden="true"></i></span>
                            <span class="btn-text"><?= _("Choose") ?></span>
                        </button>
                        <button class="btn" data-target="3" role="tab" aria-selected="false">
                            <span class="btn-icon"><i class="fa fa-fw fa-file-invoice" aria-hidden="true"></i></span>
                            <span class="btn-text"><?= _("Pay") ?></span>
                        </button>
                        <button class="btn" data-target="4" role="tab" aria-selected="false">
                            <span class="btn-icon"><i class="fa fa-fw fa-shopping-bag" aria-hidden="true"></i></span>
                            <span class="btn-text"><?= _("Buy") ?></span>
                        </button>
                        <button class="btn" data-target="5" role="tab" aria-selected="false">
                            <span class="btn-icon"><i class="fa fa-fw fa-money-check-alt" aria-hidden="true"></i></span>
                            <span class="btn-text"><?= _("Win") ?></span>
                        </button>
                        <div class="progress">
                            <div class="progress-bar"></div>
                        </div>
                    </div>

                    <div class="widget-tabs">
                        <div class="tab show" role="tabpanel" data-tab="1">
                            <div class="tab-text"><?= _("You need to open a free user account on our website.") ?></div>
                            <a class="btn btn-primary" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('signup')); ?>"><?= _("Sign Up") ?></a>
                        </div>
                        <div class="tab" role="tabpanel" data-tab="2">
                            <div class="tab-text"><?= _("Choose from 30+ official lotteries you want to play!") ?></div>
                            <a class="btn btn-primary" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play')); ?>"><?= _("Choose") ?></a>
                        </div>
                        <div class="tab" role="tabpanel" data-tab="3">
                            <div class="tab-text"><?= _("Select your preferred payment method, and make the payment.") ?></div>
                            <a class="btn btn-primary" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('lp-payment-methods')); ?>"><?= _("Payment Methods") ?></a>
                        </div>
                        <div class="tab" role="tabpanel" data-tab="4">
                            <div class="tab-text"><?= _("We buy an official lottery ticket on your behalf.") ?></div>
                            <a class="btn btn-primary" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('account')); ?>tickets/awaiting/"><?= _("My Tickets") ?></a>
                        </div>
                        <div class="tab" role="tabpanel" data-tab="5">
                            <div class="tab-text"><?= _("Your winnings will be transered directly to your account.") ?></div>
                            <a class="btn btn-primary" href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('account')); ?>tickets/"><?= _("My Tickets") ?></a>
                        </div>
                    </div>

                </div>
                <div class="col-right">
                    <div class="btn widget-video" data-video="<?php echo $video;?>" style="background-image:url(<?php echo $image;?>)"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="widget-popup">
        <div class="widget-popup-dialog">
            <div class="widget-popup-header">
                <span><?= _("How it works?") ?></span>
                <button class="btn-close">
                    <i class="fas fa-x"></i>
                </button>
            </div>
            <div class="widget-popup-body">
                <div class="widget-popup-embed"></div>
            </div>
        </div>
    </div>
<?php endif;?>
