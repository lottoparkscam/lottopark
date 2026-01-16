<p>
    <?php
        $refer_page = get_post(
    apply_filters(
        'wpml_object_id',
        lotto_platform_get_post_id_by_slug('account/referafriend'),
        'page',
        true
    )
);
                                    
        $refer_content = "";
        if (!empty($refer_page) && !empty($refer_page->post_content)) {
            $refer_content = apply_filters('the_content', $refer_page->post_content);
        } else {
            $refer_content = _('Share this link with your friend. If someone registers and makes an order, You will receive a free ticket.');
        }
                                    
        echo $refer_content;
    ?>
</p>

<div class="platform-form">
    <div class="refer-to-friend-link-wrapper" style="display: inline-block">
        <input id="myaccount-refer-link" type="text" value="<?= $refer_link ?>" class="form-control" readonly="true"/>
    </div>
    <div class="copy-to-clipboard-wrapper" style="display: inline-block">
        <button class="btn btn-primary" id="myaccount-refer-copytoclipboard"><?= _('Copy to clipboard'); ?></button>
    </div>
</div>
<div class="social-network">
    <p><?= _('Share it:'); ?></p>
    <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= Security::htmlentities($refer_link) ?>&display=page" target="_blank"><span class="fa fa-brands fa-facebook-f"></span>Facebook</a>
    <a class="twitter" href="https://twitter.com/intent/tweet?text=<?= Security::htmlentities(sprintf(_('What is your experience with %s?'), $whitelabel['name']) . ' ' . $refer_link) ?>" target="_blank"><span class="fa fa-brands fa-twitter"></span>Twitter</a>
    <a class="email" href="mailto:?subject=<?= _('Great lottery for You') ?>&body=<?= Security::htmlentities(sprintf(_('What is your experience with %s?'), $whitelabel['name']) . ' ' . $refer_link) ?>"><span class="fa fa-envelope-o"></span>Email</a>
</div>
<div class="statistics-wrapper">
    <div class="statistic">
        <div class="statistic-text float-left"><?= _('Clicks:'); ?></div>
        <div><?= $statistics->clicks ?? 0 ?></div>
    </div>
    <div class="statistic">
        <div class="statistic-text float-left"><?= _('Unique clicks:'); ?></div>
        <div><?= $statistics->unique_clicks ?? 0 ?></div>
    </div>
    <div class="statistic">
        <div class="statistic-text float-left"><?= _('Registrations:'); ?></div>
        <div><?= $statistics->registrations ?? 0 ?></div>
    </div>
    <div class="statistic">
        <div class="statistic-text float-left"><?= _('Free ticket receive:'); ?></div>
        <div><?= $statistics->free_tickets ?? 0 ?></div>
    </div>
</div>